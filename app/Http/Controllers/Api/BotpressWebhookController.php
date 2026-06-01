<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BotpressWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $payload = $request->json()->all();

        $eventType = $this->resolveEventType($payload);
        $conversationId = $this->resolveConversationId($payload);
        $messageId = $this->resolveMessageId($payload);

        Log::info('Botpress webhook event received.', [
            'event_type' => $eventType,
            'conversation_id' => $conversationId,
            'message_id' => $messageId,
            'ip' => $request->ip(),
        ]);

        return response()->noContent();
    }

    private function resolveEventType(array $payload): string
    {
        $value = Arr::get($payload, 'event.type')
            ?? Arr::get($payload, 'type')
            ?? Arr::get($payload, 'event_type')
            ?? '';

        return trim((string) $value);
    }

    private function resolveConversationId(array $payload): string
    {
        $value = Arr::get($payload, 'event.payload.conversationId')
            ?? Arr::get($payload, 'conversationId')
            ?? Arr::get($payload, 'conversation_id')
            ?? '';

        return trim((string) $value);
    }

    private function resolveMessageId(array $payload): string
    {
        $value = Arr::get($payload, 'event.payload.messageId')
            ?? Arr::get($payload, 'messageId')
            ?? Arr::get($payload, 'message_id')
            ?? '';

        return trim((string) $value);
    }
}
