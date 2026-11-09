<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlacedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order '.$this->order->order_number.' received',
        );
    }

    public function content(): Content
    {
        $lipa = Payment::lipaAccount();
        $isLipa = $this->order->payment_method === Payment::METHOD_VODACOM_LIPA;

        return new Content(
            markdown: 'mail.order-placed',
            with: [
                'order' => $this->order,
                'isLipa' => $isLipa,
                'lipaNumber' => $isLipa ? $lipa['number'] : null,
                'lipaBusinessName' => $isLipa ? $lipa['business_name'] : null,
                'lipaInstructions' => $isLipa ? $lipa['instructions'] : null,
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
