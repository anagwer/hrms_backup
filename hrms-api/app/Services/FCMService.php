<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FCMService
{
    private $factory;
    private $messaging;

    public function __construct()
    {
        $this->factory = (new Factory())
            ->withServiceAccount(config('firebase.service_account_path'));
        $this->messaging = $this->factory->createMessaging();
    }

    /**
     * @param array|mixed $data
     * @param string $token
     * @param Kreait\Firebase\Messaging\Notification $notification
     * @return array<mixed>
     * @throws Kreait\Firebase\Exception\MessagingException
     * @throws Kreait\Firebase\Exception\FirebaseException
     * @throws Kreait\Firebase\Exception\InvalidArgumentException
     */
    public function sendMessage($data, $token, $notification)
    {
        try {
            $message = CloudMessage::withTarget('token', $token)
                ->withNotification($notification)
                ->withData($data);

            return $this->messaging->send($message);
        } catch (\Exception $error) {
            throw $error;
        }
    }
}
