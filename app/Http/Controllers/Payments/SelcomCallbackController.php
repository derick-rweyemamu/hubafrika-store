<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Mail\PaymentVerifiedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Selcom\SelcomClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Handles incoming Selcom APIGW webhook callbacks.
 *
 * Excluded from CSRF verification in bootstrap/app.php.
 */
class SelcomCallbackController extends Controller
{
    private const FAILURE_STATUSES = [
        'failed', 'fail', 'cancelled', 'canceled', 'reversed', 'declined', 'expired',
    ];

    private const SUCCESS_STATUSES = [
        'completed', 'success', 'successful', 'paid', '000',
    ];

    public function __invoke(Request $request, SelcomClient $client): JsonResponse
    {
        $settings = Payment::settings();

        if ($settings['selcom_enabled'] !== '1' || ! $client->isConfigured()) {
            return response()->json([
                'result' => 'FAIL',
                'resultcode' => '503',
                'message' => 'Selcom payment gateway is disabled or not configured.',
            ], 503);
        }

        $receivedDigest = (string) $request->header('Digest', '');
        $timestamp = (string) $request->header('Timestamp', '');
        $signedFields = (string) $request->header('Signed-Fields', '');
        $payload = $request->all();

        if (! $client->verifyWebhookSignature($payload, $receivedDigest, $timestamp, $signedFields)) {
            Log::warning('Selcom callback invalid signature', [
                'digest' => $receivedDigest,
                'timestamp' => $timestamp,
                'signed_fields' => $signedFields,
                'payload' => $payload,
            ]);

            return response()->json([
                'result' => 'FAIL',
                'resultcode' => '403',
                'message' => 'Invalid signature digest',
            ], 403);
        }

        $orderId = (string) ($payload['order_id'] ?? $payload['reference'] ?? '');
        $transId = (string) ($payload['transid'] ?? $payload['channel_reference'] ?? $payload['reference'] ?? '');
        $status = strtolower((string) ($payload['payment_status'] ?? $payload['status'] ?? $payload['result'] ?? ''));

        if ($orderId === '') {
            return response()->json([
                'result' => 'FAIL',
                'resultcode' => '400',
                'message' => 'Missing order_id or reference in payload',
            ], 400);
        }

        $order = Order::query()
            ->where('order_number', $orderId)
            ->orWhere('payment_reference', $orderId)
            ->orWhereHas('payments', fn ($q) => $q->where('reference', $orderId))
            ->first();

        if ($order === null) {
            Log::warning('Selcom callback order not found', ['order_id' => $orderId, 'payload' => $payload]);

            return response()->json([
                'result' => 'FAIL',
                'resultcode' => '404',
                'message' => 'Order not found',
            ], 404);
        }

        $payment = $order->payments()
            ->where('method', Payment::METHOD_SELCOM)
            ->latest('id')
            ->first();

        if ($payment === null) {
            $payment = $order->payments()->create([
                'method' => Payment::METHOD_SELCOM,
                'amount' => $order->total,
                'currency' => $order->currency,
                'reference' => $transId ?: $orderId,
                'status' => Payment::STATUS_PENDING,
                'submitted_at' => now(),
            ]);
        }

        if ($payment->isVerified()) {
            return response()->json([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'message' => 'Order already verified',
            ]);
        }

        $isSuccess = in_array($status, self::SUCCESS_STATUSES, true);
        $isFailure = in_array($status, self::FAILURE_STATUSES, true);

        if ($isSuccess) {
            DB::transaction(function () use ($payment, $order, $transId, $payload): void {
                $payment->forceFill([
                    'status' => Payment::STATUS_VERIFIED,
                    'reference' => $transId ?: $payment->reference,
                    'payload' => $payload,
                    'verified_at' => now(),
                ])->save();

                $order->forceFill([
                    'payment_status' => 'paid',
                    'payment_reference' => $transId ?: $payment->reference,
                ])->save();
            });

            $order->refresh();
            $order->notifyCustomer(new PaymentVerifiedMail($order));

            Log::info('Selcom payment successfully verified', [
                'order_number' => $order->order_number,
                'transid' => $transId,
            ]);

            return response()->json([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'message' => 'Payment verified successfully',
            ]);
        }

        if ($isFailure) {
            DB::transaction(function () use ($payment, $order, $payload): void {
                $payment->forceFill([
                    'status' => Payment::STATUS_REJECTED,
                    'payload' => $payload,
                ])->save();

                $order->forceFill([
                    'payment_status' => 'failed',
                ])->save();
            });

            Log::info('Selcom payment marked failed', [
                'order_number' => $order->order_number,
                'status' => $status,
            ]);

            return response()->json([
                'result' => 'SUCCESS',
                'resultcode' => '000',
                'message' => 'Payment failure logged',
            ]);
        }

        return response()->json([
            'result' => 'SUCCESS',
            'resultcode' => '000',
            'message' => 'Payment status pending/acknowledged',
        ]);
    }
}
