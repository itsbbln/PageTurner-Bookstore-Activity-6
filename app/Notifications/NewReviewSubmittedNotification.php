<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReviewSubmittedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Review $review
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New book review submitted')
            ->line('A new review has been submitted for "'.$this->review->book->title.'".')
            ->line('Rating: '.$this->review->rating.'/5')
            ->line('By: '.$this->review->user->name)
            ->line('Comment: '.$this->review->comment);
    }
}

