<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\NotificationsService;
use Illuminate\Http\Request;
use Illuminate\Notifications\NotificationServiceProvider;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{

    protected $notificationService;

    public function __construct(NotificationsService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * get all unread notifications
     *
     */

    public function getUnreadNotifications()
    {
        try {
            $response = $this->notificationService->getUnreadNotifications();
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * read notification
     *
     */

    public function markAsRead($notificationId)
    {
        try {
            $response = $this->notificationService->markAsRead($notificationId);
            if ($response['status']) {
                return ResponseHelper::success($response['message'], $response['data']);
            } else {
                return ResponseHelper::error($response['message']);
            }
        } catch (\Exception $e) {
            return ResponseHelper::error($response['message']);
        }
    }
}
