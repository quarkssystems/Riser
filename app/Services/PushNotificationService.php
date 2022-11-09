<?php 

namespace App\Services;

use Pusher\PushNotifications\PushNotifications;

class PushNotificationService {

    public function send($notificationType = NULL, $data = []) {
        // Remember to change this with your cluster name.
        $pusherConfig = config('broadcasting.connections.pusher');

        $options = array(
            'cluster' => $pusherConfig['options']['cluster'],
            'encrypted' => true
        );

        // Remember to set your credentials below.
        $pusher = new Pusher(
            $pusherConfig['key'],
            $pusherConfig['secret'],
            $pusherConfig['app_id'],
            $options
        );

        // Send a message to notify channel with an event name of notify-event
        if ($notificationType == 'connection') {
        	$response['data'] = $pusher->trigger('chatChannel', 'App\\Events\\ConnectionEvent', $data);
        } else if($notificationType == 'test') {
        	$message= "Hello Test Event Message"; 
			$response['data'] = $pusher->trigger('chatChannel', 'App\\Events\\TestEvent', $message);        	
        }
        return response($response);
    }

    /**
     * Send mobile push notifications
     * Created By : Kalpesh Joshi
     * Created At : 28-July-2021
     * 
     */
    public function mobilePushNotifications($userIds=[], $title=null, $body=null, $deepLink=null, $extraData = [])
    {
        $beamsClient = new PushNotifications(
                            array(
                                "instanceId" => config('constant.PUSHER_BEAM_INSTANCE_ID'),
                                "secretKey" => config('constant.PUSHER_BEAM_SECRET_KEY'),
                            )
                        );

        $notificationData = [];
        if ($extraData) {
            $notificationData = array(
                "fcm" => array(
                  "notification" => array(
                    "title" => $title,
                    "body" => $body
                  ),
                  "data" => $extraData
                ),
                "apns" => array("aps" => array(
                  "alert" => array(
                    "title" => $title,
                    "body" => $body
                  ),
                  "data" => $extraData
                )),
                "web" => array(
                  "notification" => array(
                    "title" => $title,
                    "body" => $body,
                    "deep_link" => $deepLink
                  ),
                  "data" => $extraData
                )
            );
        } else {
            $notificationData = array(
                "fcm" => array(
                  "notification" => array(
                    "title" => $title,
                    "body" => $body
                  )
                ),
                "apns" => array("aps" => array(
                  "alert" => array(
                    "title" => $title,
                    "body" => $body
                  )
                )),
                "web" => array(
                  "notification" => array(
                    "title" => $title,
                    "body" => $body,
                    "deep_link" => $deepLink
                  )
                )
            );
        }

        $publishResponse = $beamsClient->publishToUsers($userIds, $notificationData);

        if ($publishResponse) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

}