<?php


namespace App\Services;

use App\Helpers\ErrorLogger;


class NotificationsService
{

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * get all unread notifications
     *
     */

    public function getUnreadNotifications()
    {
        try {
            $user = auth()->user();
            $notifications = $user->unreadNotifications;
            return ['status' => true, 'message' => 'Notifications fetched successfully', 'data' => $notifications];
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
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
            $user = auth()->user();
            $notification = $user->unreadNotifications->where('id', $notificationId)->first();
            if ($notification) {
                $notification->markAsRead();
                return ['status' => true, 'message' => 'Notification marked as read successfully'];
            } else {
                return ['status' => false, 'message' => 'Notification not found'];
            }
        } catch (\Exception $e) {
            ErrorLogger::logError($e);
            return ['status' => false, 'message' => 'Something went wrong'];
        }
    }
}
