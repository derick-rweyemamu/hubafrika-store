<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Rules\RecaptchaV3;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactInquiryController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', 'max:191'],
            'subject' => ['nullable', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
            'g-recaptcha-response' => [new RecaptchaV3('contact')],
        ]);

        $recipient = BusinessSetting::query()->where('type', 'contact_email')->value('value');
        $subject = filled($validated['subject'] ?? null)
            ? 'Website Inquiry: '.trim((string) $validated['subject'])
            : 'Website Inquiry from '.$validated['name'];

        if (is_string($recipient) && filter_var(trim($recipient), FILTER_VALIDATE_EMAIL) !== false) {
            try {
                $body = "Name: {$validated['name']}\nEmail: {$validated['email']}\n\n{$validated['message']}";
                Mail::raw($body, function ($mail) use ($recipient, $subject, $validated): void {
                    $mail->to(trim($recipient))
                        ->replyTo($validated['email'], $validated['name'])
                        ->subject($subject);
                });
            } catch (\Throwable $e) {
                Log::warning('Contact inquiry mail could not be sent: '.$e->getMessage());
            }
        } else {
            Log::info('Contact inquiry received (no contact_email configured)', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $subject,
            ]);
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('Inquiry received. Thank you!'),
            ]);
        }

        return back()->with('contact_status', __('Inquiry received. Thank you!'));
    }
}
