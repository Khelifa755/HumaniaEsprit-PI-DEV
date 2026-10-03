<?php

namespace App\Service\Plannification;

use App\Entity\Reunion;
use Psr\Log\LoggerInterface;

/**
 * Applies Zoom create/update/delete rules for {@see Reunion} (plannification module).
 * After every successful Zoom creation, an invitation e-mail is sent via Brevo.
 */
final class ReunionZoomIntegration
{
    private const ZOOM_USER_FAILURE =
        'La visioconférence n\'a pas pu être créée. La réunion a été enregistrée sans lien Zoom.';

    public function __construct(
        private readonly ZoomService      $zoom,
        private readonly BrevoMailer      $brevoMailer,
        private readonly LoggerInterface  $logger,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * After a valid "new reunion" form: create Zoom only when online, then send invitation emails.
     */
    public function syncNewReunion(Reunion $reunion): ZoomSyncOutcome
    {
        if (!$reunion->getEnLigne()) {
            $this->clearZoomFields($reunion);
            return ZoomSyncOutcome::skipped();
        }

        try {
            $duration = $this->computeDurationMinutes(
                $reunion->getDateHeureDebut(),
                $reunion->getDateHeureFin(),
            );

            $zoomData = $this->zoom->createMeeting(
                $reunion->getTitre(),
                $reunion->getDateHeureDebut(),
                $duration,
                $reunion->getDescription() ?? '',
            );

            $reunion->setZoom_meeting_id($zoomData['meeting_id']);
            $reunion->setZoom_join_url($zoomData['join_url']);
            $reunion->setZoom_start_url($zoomData['start_url']);
            $reunion->setZoom_password($zoomData['password']);

            // ── Send invitation e-mails to all participants ──────────────────
            $emailResult = $this->brevoMailer->sendZoomInvitation($reunion);
            $this->logger->info('BrevoMailer: invitation emails sent (new reunion)', [
                'reunion_id' => $reunion->getId(),
                'sent'       => $emailResult['sent'],
                'failed'     => $emailResult['failed'],
            ]);

            return ZoomSyncOutcome::ok($zoomData['join_url'] ?: null);

        } catch (\Throwable $e) {
            $this->logger->error('Zoom meeting creation failed (new reunion)', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            $this->clearZoomFields($reunion);

            return ZoomSyncOutcome::failed(self::ZOOM_USER_FAILURE);
        }
    }

    /**
     * After a valid "edit reunion" form: sync Zoom and resend invitation when the
     * meeting was just switched to online or already online and updated.
     *
     * @param string|null $oldMeetingId Zoom id before the form was applied.
     */
    public function syncEditedReunion(Reunion $reunion, bool $wasOnline, ?string $oldMeetingId): void
    {
        $isNowOnline = $reunion->getEnLigne();

        try {
            if (!$wasOnline && $isNowOnline) {
                // Offline → Online: create a new Zoom meeting and notify participants.
                $duration = $this->computeDurationMinutes(
                    $reunion->getDateHeureDebut(),
                    $reunion->getDateHeureFin(),
                );
                $zoomData = $this->zoom->createMeeting(
                    $reunion->getTitre(),
                    $reunion->getDateHeureDebut(),
                    $duration,
                    $reunion->getDescription() ?? '',
                );
                $reunion->setZoom_meeting_id($zoomData['meeting_id']);
                $reunion->setZoom_join_url($zoomData['join_url']);
                $reunion->setZoom_start_url($zoomData['start_url']);
                $reunion->setZoom_password($zoomData['password']);

                $this->sendInvitations($reunion, 'edit-online');

            } elseif ($wasOnline && !$isNowOnline) {
                // Online → Offline: remove Zoom meeting; no e-mail needed.
                $this->zoom->deleteMeeting((string) $oldMeetingId);
                $this->clearZoomFields($reunion);

            } elseif ($wasOnline && $isNowOnline && $oldMeetingId && $oldMeetingId !== '0') {
                // Online → Online: update existing meeting and resend updated invitation.
                $this->zoom->updateMeeting(
                    (string) $oldMeetingId,
                    $reunion->getTitre(),
                    $reunion->getDateHeureDebut(),
                    $this->computeDurationMinutes(
                        $reunion->getDateHeureDebut(),
                        $reunion->getDateHeureFin(),
                    ),
                    $reunion->getDescription() ?? '',
                );
                $this->sendInvitations($reunion, 'edit-updated');
            }

        } catch (\Throwable $e) {
            $this->logger->error('Zoom sync failed (edit reunion)', [
                'reunion_id'     => $reunion->getId(),
                'was_online'     => $wasOnline,
                'is_now_online'  => $isNowOnline,
                'old_meeting_id' => $oldMeetingId,
                'exception'      => $e->getMessage(),
            ]);
        }
    }

    public function deleteZoomMeetingIfAny(Reunion $reunion): void
    {
        if (!$reunion->getEnLigne()) {
            return;
        }
        $id = $reunion->getZoom_meeting_id();
        if (!$id || $id === '0') {
            return;
        }
        try {
            $this->zoom->deleteMeeting($id);
        } catch (\Throwable $e) {
            $this->logger->warning('Zoom delete on reunion removal failed', [
                'meeting_id' => $id,
                'exception'  => $e->getMessage(),
            ]);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function sendInvitations(Reunion $reunion, string $context): void
    {
        try {
            $result = $this->brevoMailer->sendZoomInvitation($reunion);
            $this->logger->info('BrevoMailer: invitation emails sent', [
                'context'    => $context,
                'reunion_id' => $reunion->getId(),
                'sent'       => $result['sent'],
                'failed'     => $result['failed'],
            ]);
        } catch (\Throwable $e) {
            // Non-blocking: log but do not bubble up.
            $this->logger->error('BrevoMailer: unexpected error during invitation send', [
                'context'    => $context,
                'reunion_id' => $reunion->getId(),
                'exception'  => $e->getMessage(),
            ]);
        }
    }

    private function clearZoomFields(Reunion $reunion): void
    {
        $reunion->setZoom_meeting_id('0');
        $reunion->setZoom_join_url('');
        $reunion->setZoom_start_url('');
        $reunion->setZoom_password('');
    }

    private function computeDurationMinutes(\DateTimeInterface $start, \DateTimeInterface $end): int
    {
        $diff = $end->getTimestamp() - $start->getTimestamp();
        return max(15, (int) round($diff / 60));
    }
}