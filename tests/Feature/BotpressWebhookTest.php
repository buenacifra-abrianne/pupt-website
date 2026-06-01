<?php

namespace Tests\Feature;

use Tests\TestCase;

class BotpressWebhookTest extends TestCase
{
    public function test_webhook_rejects_missing_or_invalid_secret(): void
    {
        config(['services.botpress.webhook_secret' => 'expected-secret']);

        $this->postJson('/api/botpress/webhook', [
            'event' => [
                'type' => 'webchat:conversationStarted',
            ],
        ])->assertStatus(401);

        $this->withHeaders([
            'X-BP-SECRET' => 'wrong-secret',
        ])->postJson('/api/botpress/webhook', [
            'event' => [
                'type' => 'webchat:conversationStarted',
            ],
        ])->assertStatus(401);
    }

    public function test_webhook_accepts_signed_conversation_started_events(): void
    {
        config(['services.botpress.webhook_secret' => 'expected-secret']);

        $this->withHeaders([
            'X-BP-SECRET' => 'expected-secret',
        ])->postJson('/api/botpress/webhook', [
            'event' => [
                'type' => 'webchat:conversationStarted',
                'payload' => [
                    'conversationId' => 'conv-123',
                    'messageId' => 'msg-456',
                ],
            ],
        ])->assertNoContent();
    }
}
