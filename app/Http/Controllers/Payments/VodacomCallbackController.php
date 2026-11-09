<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Mail\PaymentVerifiedMail;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Receives Vodacom M-Pesa (Lipa) payment callbacks.
 *
 * The store verifies Lipa payments manually until a merchant shortcode and API
 * credentials exist; this endpoint is already shaped for that switch-over. It is
 * inert until "Callback signing secret" is set in the payment settings.
 */
class VodacomCallbackController extends Controller
{
    /**
     * Callback statuses that mean the money never arrived.
     *
     * @var array<int, string>
     */
    private const FAILURE_STATUSES = ['failed', 'cancelled', 'canceled', 'reversed', 'declined'];

    public function __invoke(Request $request): JsonResponse
    {
        $secret = Payment::settings()['lipa_webhook_secret'];

        if ($secret === '') {
            return response()->json(['status' => 'disabled'], 503);
        }

        $signature = (string) $request->header('X-Vodacom-Signature', '');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if ($signature === '' || ! hash_equals($expected, $signature)) {
            return response()->json(['status' => 'invalid signature'], 403);
        }

        $data = $request->validate([
            'reference' => ['required', 'string', 'max:100'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'string', 'max:30'],
            'payer_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $payment = Payment::query()->where('reference', $data['reference'])->first();

        if ($payment === null) {
            return response()->json(['status' => 'unknown reference'], 404);
        }

        if ($payment->isVerified()) {
            return response()->json(['status' => 'already verified']);
        }

        $isFailure = in_array(strtolower((string) ($data['status'] ?? '')), self::FAILURE_STATUSES, true);

        DB::transaction(function () use ($payment, $data, $isFailure): void {
            $payment->forceFill([
                'status' => $isFailure ? Payment::STATUS_REJECTED : Payment::STATUS_VERIFIED,
                'payer_phone' => $data['payer_phone'] ?? $payment->payer_phone,
                'payload' => $data,
                'verified_at' => now(),
            ])->save();

            $order = Order::query()->lockForUpdate()->find($payment->order_id);

            if ($order === null) {
                return;
            }

            $order->payment_status = $isFailure ? 'failed' : 'paid';
            $order->payment_reference = $payment->reference;
            $order->save();
        });

        if (! $isFailure) {
            $order = Order::query()->find($payment->order_id);

            if ($order !== null) {
                $order->notifyCustomer(new PaymentVerifiedMail($order));
            }
        }

        return response()->json(['status' => $isFailure ? 'rejected' : 'verified']);
    }
}
