<?php

namespace App\Support;

class ApprovalAudit
{
    public static function module(?string $type): string
    {
        $type = strtoupper(trim((string) $type));

        return match (true) {
            str_starts_with($type, 'ANNOUNCEMENT_') => 'ANNOUNCEMENT',
            str_starts_with($type, 'NEWS_') => 'NEWS',
            str_starts_with($type, 'DOWNLOADABLE_') => 'DOWNLOADABLE',
            default => 'CONTENT',
        };
    }

    public static function description(object $request): string
    {
        return 'Approved '.strtoupper(trim((string) $request->type)).': '
            .PlainText::normalize($request->title ?? 'Request')
            .' [Approval request #'.$request->id.']';
    }

    public static function record(object $request): void
    {
        AuditLog::record(
            'APPROVED',
            self::module($request->type),
            self::description($request),
            (int) $request->id,
            ['approval_request_id' => (int) $request->id],
            required: true
        );
    }
}
