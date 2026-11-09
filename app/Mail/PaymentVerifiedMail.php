<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentVerifiedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment confirmed for order '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payment-verified',
            with: [
                'order' => $this->order,
                'reference' => $this->order->payment_reference,
                'paymentMethodLabel' => $this->order->paymentMethodLabel(),
                'orderUrl' => route('orders.show', $this->order),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
