<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DataInputNotification extends Notification
{
    use Queueable;

    public $title;
    public $message;
    public $url;
    public $type;
    public $pdfUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $message, $url, $type = 'info', $pdfUrl = null)
    {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->type = $type;
        $this->pdfUrl = $pdfUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'pdf_url' => $this->pdfUrl,
            'type' => $this->type,
        ];
    }
}
