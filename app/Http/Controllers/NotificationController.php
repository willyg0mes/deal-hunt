<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->with('game')
            ->orderByDesc('created_at')
            ->get();

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead($id, Request $request)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->update(['read' => true]);

        $redirectUrl = $request->header('referer', route('home'));
        return redirect()->to($redirectUrl)->with('status', 'Notificação marcada como lida!');
    }
    
    public function markAllAsRead(Request $request)
    {
        auth()->user()->notifications()->where('read', false)->update(['read' => true]);
        
        $redirectUrl = $request->header('referer', route('home'));
        return redirect()->to($redirectUrl)->with('status', 'Todas as notificações foram marcadas como lidas.');
    }


    public function destroy($id, Request $request)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        $redirectUrl = $request->header('referer', route('home'));
        return redirect()->to($redirectUrl)->with('status', 'Notificação removida.');
    }
}
