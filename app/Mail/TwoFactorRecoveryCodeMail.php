<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorRecoveryCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public int $expiresInMinutes = 15,
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Your two-factor recovery code'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $formattedCode = strlen($this->code) === 8
            ? substr($this->code, 0, 4).'-'.substr($this->code, 4, 4)
            : $this->code;

        return new Content(
            markdown: 'mail.two-factor-recovery-code',
            with: [
                'user' => $this->user,
                'formattedCode' => $formattedCode,
                'expiresInMinutes' => $this->expiresInMinutes,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
