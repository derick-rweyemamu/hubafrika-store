<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing('items');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order '.$this->order->order_number.' is on its way',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.order-shipped',
            with: [
                'order' => $this->order,
                'deliveryAddress' => collect([
                    $this->order->shipping_address,
                    $this->order->shipping_city,
                    $this->order->shipping_state,
                    $this->order->shipping_country,
                ])->filter()->implode(', '),
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
