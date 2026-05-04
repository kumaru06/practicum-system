<?php

namespace App\Http\Controllers;

use App\Services\PracticumService;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function markAllRead(PracticumService $p): RedirectResponse
    {
        $user = $p->currentUser();
        if (!$user) {
            return redirect()->route('login');
        }

        $p->notificationsMarkAllRead((int)$user['id']);

        return back()->with('success', 'Notifications marked as read.');
    }
}