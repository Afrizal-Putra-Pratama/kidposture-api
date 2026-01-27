<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\Screening;

class NotificationHelper
{
    public static function sendToParent($screeningId, $type, $title, $message)
    {
        // pakai relasi child.user (bukan child.parent)
        $screening = Screening::with('child.user')->find($screeningId);

        if (
            !$screening ||
            !$screening->child ||
            !$screening->child->user
        ) {
            return null;
        }

        return Notification::create([
            'user_id'      => $screening->child->user->id,
            'screening_id' => $screeningId,
            'type'         => $type,
            'title'        => $title,
            'message'      => $message,
        ]);
    }
}
