<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\View\View;

abstract class Controller
{
    protected function user(): ?array
    {
        return session('user');
    }

    protected function requireRole(PracticumService $practicum, string|array $roles): array
    {
        return $practicum->requireRole($roles);
    }

    protected function renderNative(PracticumService $practicum, string $view, array $data = []): View
    {
        $user = $this->user();
        $notifications = [];
        $unreadNotifications = 0;
        $studentProfileCompleted = true;

        if ($user) {
            $notifications = $practicum->notificationsRecentForUser((int)$user['id']);
            $unreadNotifications = $practicum->notificationsUnreadCount((int)$user['id']);
            if (($user['role'] ?? '') === 'student') {
                $student = $practicum->studentFindByUser((int)$user['id']);
                $studentProfileCompleted = (int)($student['profile_completed'] ?? 0) === 1;
            }
        }

        return view('layouts.app', array_merge($data, [
            'contentView' => $view,
            'user' => $user,
            'notifications' => $notifications,
            'unreadNotifications' => $unreadNotifications,
            'studentProfileCompleted' => $studentProfileCompleted,
        ]));
    }
}
