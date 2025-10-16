<?php

namespace App\Services;

use App\Helpers\TranslationHelper;
use App\Models\Client;
use App\Models\Coach;
use App\Models\Driver;
use App\Models\OperationNotification as ModelsNotification;
use App\Models\User\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(storage_path('app/firebase/laravel-firebase-fec.json'));

        $this->messaging = $firebase->createMessaging();
    }
    // StoreAndPushNotification
    public function storeAndPushNotification($to_id)
    {

            $driver = User::find($to_id);
            $title = $driver->default_language == 'ar' ? $title_ar : $title_en;
            $body = $driver->default_language == 'ar' ? $body_ar : $body_en;
            $driver_id = $driver->id;
            $token = $driver->fcm_token;

        //artived
        // Store the notification in the database
        //  ModelsNotification::create([
        //     'title' => $title_en,
        //     'title_ar' => $title_ar,
        //     'message' => $body_en,
        //     'message_ar' => $body_ar,
        //     'type' => $to=='driver' ? 'drivers' : 'clients',
        //     'client_id' => $to == 'client' ? $to_id : null,
        //     'driver_id' => $to == 'driver' ? $to_id : null,
        // ]);

        try {
            $this->sendToDevice($token, $title, $body);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    /**
     * Send notification to a single device
     */
    public function sendToDevice($token, $title, $body, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        try {
            $this->messaging->send($message);
            return ['success' => true, 'message' => 'Notification sent successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToMultipleDevices($tokens, $title, $body, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::new()
            ->withNotification($notification)
            ->withData($data);

        try {
            $response = $this->messaging->sendMulticast($message, $tokens);

            return [
                'success' => true,
                'successful_sends' => $response->successes()->count(),
                'failed_sends' => $response->failures()->count()
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send notification to a topic
     */
    public function sendToTopic($topic, $title, $body, $data = [])
    {
        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('topic', $topic)
            ->withNotification($notification)
            ->withData($data);

        try {
            $this->messaging->send($message);
            return ['success' => true, 'message' => 'Topic notification sent successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Subscribe devices to a topic
     */
    public function subscribeToTopic($tokens, $topic)
    {
        try {
            $this->messaging->subscribeToTopic($topic, $tokens);
            return ['success' => true, 'message' => 'Subscribed to topic successfully'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
