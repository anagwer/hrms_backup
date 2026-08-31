<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPinNotification extends Notification
{
    use Queueable;

    private string $receiverName;

    private string $pin;

    /**
     * Create a new notification instance.
     *
     * @param string $receiverName
     * @param string $pin
     * @return void
     */
    public function __construct($receiverName, $pin)
    {
        $this->receiverName = $receiverName;
        $this->pin = $pin;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->greeting("Halo {$this->receiverName}.")
            ->line('Permintaan reset PIN telah berhasil dilakukan.  Silahkan catat pin berikut di tempat yang aman.')
            ->line(new HtmlString("<span style='color: black; font-size: 30px; margin:auto;'><b>$this->pin</b></span>"))
            ->line('Jangan beritahukan PIN ini ke siapapun.')
            ->success();

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
