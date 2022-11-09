<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use williamcruzme\FCM\Messages\FcmMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BeforeMasterClass extends Notification implements ShouldQueue
{
    use Queueable;

    protected $message, $masterClassData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($message, $masterClassData)
    {
        $this->message = $message;
        $this->masterClassData = $masterClassData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['fcm', 'database'];
    }

    /**
     * Get the Firebase Message representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Williamcruzme\Fcm\Messages\FcmMessage
     */
    public function toFcm($notifiable)
    {
        return (new FcmMessage)
                    ->notification([
                        'title' => $this->message,
                        'body'  => 'Tap here to take action',
                    ])
                    ->data([
                        'type'            => 'master_class',
                        'master_class_id' => $this->masterClassData->id,
                    ]);
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
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

    public function toDatabase($notifiable)
    {
        return [
            'title'           => $this->message,
            'type'            => 'master_class',
            'master_class_id' => $this->masterClassData->id,
        ];
    }
}
