<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Notifications\UserRegistered;

class NotificationController extends Controller
{
    protected $adminType = [
        'App\\Notifications\\UserRegistered'
    ];
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function user()
    {
        $user = auth()->user();
        $notifications = $user->notifications->filter(function ($notification) {
            return $notification->data['type'] != 'admin';
        });
        return $notifications;
    }

    public function userRead()
    {
        $notifications = $this->user();
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        return response()->json(null, 204);
    }

    public function admin()
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            return response()->json([
                'errors' => [
                    'message' => 'This action is unauthorized.'
                ]
            ], 403);
        }
        $notifications = $user->notifications->filter(function ($notification) {
            return $notification->data['type'] == 'admin';
        });
        return $notifications;
    }

    public function adminRead()
    {
        $notifications = $this->admin();
        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }
        return response()->json(null, 204);
    }
}
