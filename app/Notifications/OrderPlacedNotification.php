<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your order has been placed')
            ->line('Thank you for your order at PageTurner!')
            ->line('Order #'.$this->order->id.' Total: ₱'.number_format($this->order->total_amount, 2))
            ->action('View Order', route('orders.show', $this->order))
            ->line('We will notify you when the status of your order changes.');
    }
}

