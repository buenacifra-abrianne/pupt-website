<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class AccountStatusGuard
{
    public const SELF_DEACTIVATION_MESSAGE = 'You cannot suspend or deactivate your own account.';

    public static function denySelfDeactivation(int $targetId, string $status): ?JsonResponse
    {
        $actorId = (int) session('user_id');
        if ($actorId > 0 && $actorId === $targetId
            && in_array(strtoupper(trim($status)), ['INACTIVE', 'SUSPENDED'], true)) {
            return response()->json([
                'ok' => false,
                'message' => self::SELF_DEACTIVATION_MESSAGE,
            ], 403);
        }

        return null;
    }
}
