<?php

namespace App\Services;

use Resend;
use Illuminate\Support\Facades\Log;

class ResendEmailService
{
    protected $resend;
    protected $fromEmail;

    public function __construct()
    {
        $apiKey = env('RESEND_API_KEY');
        $this->fromEmail = env('RESEND_FROM_EMAIL', 'noreply@yourdomain.com');

        if ($apiKey) {
            $this->resend = Resend::client($apiKey);
        }
    }

    /**
     * Send notification to admins about a pending approval request.
     *
     * @param array $adminEmails
     * @param array $requestDetails
     */
    public function sendPendingApprovalNotification(array $adminEmails, array $requestDetails)
    {
        if (!$this->resend || empty($adminEmails)) {
            return;
        }

        $title = $requestDetails['title'] ?? 'Request';
        $type = $requestDetails['type'] ?? 'Unknown Type';
        $friendlyType = $this->getFriendlyType($type);
        $requesterName = $requestDetails['requester_name'] ?? 'Staff';
        $date = $requestDetails['created_at'] ?? now()->format('Y-m-d H:i:s');
        $data = [
            'requesterName' => $requesterName,
            'type' => $friendlyType,
            'title' => $title,
            'date' => $date,
        ];
        
        $html = view('emails.pending_approval', $data)->render();

        app()->terminating(function () use ($adminEmails, $html) {
            foreach ($adminEmails as $email) {
                try {
                    $this->resend->emails->send([
                        'from' => 'PUP-Taguig Website CMS <' . $this->fromEmail . '>',
                        'to' => [$email],
                        'subject' => 'Action Required: New Pending Approval Request',
                        'html' => $html,
                    ]);
                } catch (\Exception $e) {
                    Log::error('ResendEmailService: Failed to send pending approval notification to ' . $email, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });
    }

    /**
     * Send notification to original requester about approval/rejection.
     *
     * @param string $staffEmail
     * @param array $requestDetails
     * @param string $status
     * @param string|null $reason
     */
    public function sendApprovalResultNotification(string $staffEmail, array $requestDetails, string $status, ?string $reason = null)
    {
        if (!$this->resend || empty($staffEmail)) {
            return;
        }

        $title = $requestDetails['title'] ?? 'Request';
        $type = $requestDetails['type'] ?? 'Unknown Type';
        $friendlyType = $this->getFriendlyType($type);
        $requesterName = $requestDetails['requester_name'] ?? 'Staff';
        $date = now()->format('Y-m-d H:i:s');

        $data = [
            'requesterName' => $requesterName,
            'type' => $friendlyType,
            'title' => $title,
            'date' => $date,
            'status' => $status,
            'reason' => $reason,
        ];
        
        $html = view('emails.approval_result', $data)->render();

        $statusText = ucfirst(strtolower($status));
        app()->terminating(function () use ($staffEmail, $statusText, $html) {
            try {
                $this->resend->emails->send([
                    'from' => 'PUP-Taguig Website CMS <' . $this->fromEmail . '>',
                    'to' => [$staffEmail],
                    'subject' => "Your Approval Request has been {$statusText}",
                    'html' => $html,
                ]);
            } catch (\Exception $e) {
                Log::error('ResendEmailService: Failed to send approval result notification to ' . $staffEmail, [
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    private function getFriendlyType(string $type): string
    {
        $typeMap = [
            'ANNOUNCEMENT_CREATE' => 'Create Announcement',
            'ANNOUNCEMENT_UPDATE' => 'Edit Announcement',
            'ANNOUNCEMENT_DELETE' => 'Delete Announcement',
            'ANNOUNCEMENT_ENABLE' => 'Enable Announcement',
            'ANNOUNCEMENT_DISABLE' => 'Disable Announcement',
            'NEWS_CREATE' => 'Create News',
            'NEWS_UPDATE' => 'Edit News',
            'NEWS_DELETE' => 'Delete News',
            'CMS_HOME_EDIT' => 'Edit Home Content',
            'CMS_ABOUT_EDIT' => 'Edit About Content',
            'CMS_ACADEMICS_EDIT' => 'Edit Academics Content',
            'CMS_STUDENTS_EDIT' => 'Edit Students Content',
            'CMS_RESEARCH_EXTENSION_EDIT' => 'Edit Research & Extension Content',
            'CMS_EVENTS_EDIT' => 'Edit Events Content',
            'DOWNLOADABLE_CREATE' => 'Create Downloadable',
            'DOWNLOADABLE_UPDATE' => 'Edit Downloadable',
            'DOWNLOADABLE_DELETE' => 'Delete Downloadable',
        ];

        return $typeMap[strtoupper($type)] ?? $type;
    }
}
