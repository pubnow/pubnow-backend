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
        $notifications = $user->unreadNotifications->filter(function ($notification) {
            return $notification->data['type'] != 'admin';
        });
        return $notifications;
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
        $notifications = $user->unreadNotifications->filter(function ($notification) {
            return $notification->data['type'] == 'admin';
        });
        return $notifications;
    }
}
