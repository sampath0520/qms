<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SystemNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $notificationData;
    protected $patient;

    public function __construct($patient, $message, $clinic)
    {
        $this->patient = $patient;

        $this->notificationData = [
            "message" => $message,
            "clinic" => $clinic
        ];
    }

    public function toDatabase($notifiable)
    {
        //get clinic details
        return [
            $this->notificationData
        ];
    }

    // Define the 'via' method to specify the notification channels
    public function via($notifiable)
    { 
        return [DatabaseChannel::class]; // Use the database channel to store the notification
    }
}
