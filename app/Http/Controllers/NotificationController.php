<?php
namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    // Récupérer les notifs non lues pour le rôle connecté
    public function index()
    {
        $role = auth()->user()->role->nom;
        $userId = auth()->id();

        $notifs = Notification::where('lue', false)
            ->where(function ($q) use ($role, $userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->whereNotNull('user_id')
                        ->where('user_id', $userId);
                })->orWhere(function ($sub) use ($role) {
                    $sub->whereNull('user_id')
                        ->where(function ($sub2) use ($role) {
                            $sub2->whereNull('roles')
                                ->orWhereJsonContains('roles', $role);
                        });
                });
            })
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'count'  => $notifs->count(),
            'items'  => $notifs,
        ]);
    }

    // Marquer une notif comme lue
    public function marquerLue(Notification $notification)
{
    $notification->update(['lue' => true]);
    return response()->json([
        'success' => true,
        'id'      => $notification->id,
    ]);
}

    // Marquer toutes comme lues
    public function toutesLues()
    {
        $role = auth()->user()->role->nom;
        $userId = auth()->id();

        Notification::where('lue', false)
            ->where(function ($q) use ($role, $userId) {
                $q->where(function ($sub) use ($userId) {
                    $sub->whereNotNull('user_id')
                        ->where('user_id', $userId);
                })->orWhere(function ($sub) use ($role) {
                    $sub->whereNull('user_id')
                        ->where(function ($sub2) use ($role) {
                            $sub2->whereNull('roles')
                                ->orWhereJsonContains('roles', $role);
                        });
                });
            })
            ->update(['lue' => true]);

        return response()->json(['success' => true, 'message' => 'Tout marqué comme lu.']);
    }
}