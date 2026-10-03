<?php

namespace App\Service\Plannification;

use App\Entity\Reunion;
use Brevo\Client\Api\TransactionalEmailsApi;
use Brevo\Client\Configuration;
use Brevo\Client\Model\SendSmtpEmail;
use Brevo\Client\Model\SendSmtpEmailSender;
use Brevo\Client\Model\SendSmtpEmailTo;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

/**
 * Sends Zoom invitations via Brevo transactional email API (SDK v1 / Brevo\Client).
 */
final class BrevoMailer
{
    private readonly TransactionalEmailsApi $api;

    public function __construct(
        string $brevoApiKey,
        bool $brevoVerifySsl,
        private readonly string $mailerFrom,
        private readonly LoggerInterface $logger,
    ) {
        $config = Configuration::getDefaultConfiguration()
            ->setApiKey('api-key', $brevoApiKey);

        $this->api = new TransactionalEmailsApi(
            new Client([
                'timeout' => 15,
                'verify'  => $brevoVerifySsl,
            ]),
            $config,
        );
    }

    /**
     * Send a Zoom invitation to every address listed in $reunion->getParticipants().
     * Participants are stored as a list of e-mail addresses (commas, semicolons or newlines).
     *
     * @return array{sent: int, failed: int}
     */
    public function sendZoomInvitation(Reunion $reunion): array
    {
        $emails = $this->parseEmails($reunion->getParticipants() ?? '');

        if (empty($emails)) {
            $this->logger->info('BrevoMailer: no recipients – skipping email send.', [
                'reunion_id' => $reunion->getId(),
            ]);

            return ['sent' => 0, 'failed' => 0];
        }

        $html   = $this->buildHtml($reunion);
        $sent   = 0;
        $failed = 0;

        foreach ($emails as $email) {
            $message = new SendSmtpEmail();
            $message->setSubject('Invitation : ' . $reunion->getTitre());
            $message->setHtmlContent($html);
            $message->setSender(new SendSmtpEmailSender([
                'name'  => 'Calendrier des Réunions',
                'email' => $this->mailerFrom,
            ]));
            $message->setTo([
                new SendSmtpEmailTo(['email' => $email]),
            ]);

            try {
                $this->api->sendTransacEmail($message);
                ++$sent;
            } catch (\Throwable $e) {
                ++$failed;
                $this->logger->error('BrevoMailer: failed to send invitation email', [
                    'recipient'  => $email,
                    'reunion_id' => $reunion->getId(),
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /** @return list<string> */
    private function parseEmails(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/u', $raw, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === false) {
            return [];
        }

        $out = [];
        foreach ($parts as $part) {
            $e = strtolower(trim($part));
            if ($e !== '' && filter_var($e, FILTER_VALIDATE_EMAIL)) {
                $out[$e] = $e;
            }
        }

        return array_values($out);
    }

    // -------------------------------------------------------------------------
    // HTML e-mail template
    // -------------------------------------------------------------------------

    private function buildHtml(Reunion $reunion): string
    {
        $title     = $reunion->getTitre() ?? 'Réunion';
        $organizer = $reunion->getNomOrganisateur() ?? 'Organisateur inconnu';
        $joinUrl   = $reunion->getZoom_join_url() ?? '#';
        $meetId    = $reunion->getZoom_meeting_id() ?? '—';
        $password  = $reunion->getZoom_password() ?? '';

        $start   = $reunion->getDateHeureDebut();
        $end     = $reunion->getDateHeureFin();
        $dateStr = $start ? $this->formatDate($start, 'fr_FR') : '—';
        $timeStr = ($start && $end)
            ? ($start->format('H:i') . ' – ' . $end->format('H:i'))
            : '—';

        $passwordRow = $password !== ''
            ? "<tr><td style='padding:8px 0;border-bottom:1px solid #f1f5f9;'>"
              . "<span style='font-size:12px;color:#64748b;'>🔒&nbsp; Mot de passe</span>"
              . "<span style='float:right;font-size:12px;font-weight:600;color:#1e293b;font-family:monospace;'>"
              . $this->e($password)
              . '</span></td></tr>'
            : '';

        return '<!DOCTYPE html>'
            . "<html lang='fr'><head><meta charset='UTF-8'>"
            . "<meta name='viewport' content='width=device-width,initial-scale=1'>"
            . '<title>Invitation réunion</title></head>'
            . "<body style='margin:0;padding:0;background:#f1f5f9;font-family:Arial,sans-serif;'>"

            . "<table width='100%' cellpadding='0' cellspacing='0' style='background:#f1f5f9;padding:32px 0;'><tr><td align='center'>"
            . "<table width='560' cellpadding='0' cellspacing='0' style='background:white;border-radius:12px;"
            . "box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;'>"

            . "<tr><td style='background:linear-gradient(135deg,#667eea,#764ba2);padding:32px 40px;text-align:center;'>"
            . "<div style='font-size:36px;margin-bottom:8px;'>📅</div>"
            . "<h1 style='color:white;margin:0;font-size:22px;font-weight:700;'>" . $this->e($title) . '</h1>'
            . "<p style='color:rgba(255,255,255,0.85);margin:8px 0 0;font-size:14px;'>Invitation à une réunion en ligne</p>"
            . '</td></tr>'

            . "<tr><td style='padding:28px 40px 0;'>"
            . "<table width='100%' cellpadding='0' cellspacing='0' style='background:#f8fafc;border-radius:8px;border:1px solid #e2e8f0;'>"
            . '<tr>'
            . "  <td style='padding:16px 20px;border-right:1px solid #e2e8f0;'>"
            . "    <div style='font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;'>DATE</div>"
            . "    <div style='font-size:14px;color:#1e293b;font-weight:600;margin-top:4px;'>" . $this->e($dateStr) . '</div>'
            . '  </td>'
            . "  <td style='padding:16px 20px;border-right:1px solid #e2e8f0;'>"
            . "    <div style='font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;'>HORAIRE</div>"
            . "    <div style='font-size:14px;color:#1e293b;font-weight:600;margin-top:4px;'>" . $this->e($timeStr) . '</div>'
            . '  </td>'
            . "  <td style='padding:16px 20px;'>"
            . "    <div style='font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;'>ORGANISATEUR</div>"
            . "    <div style='font-size:14px;color:#1e293b;font-weight:600;margin-top:4px;'>" . $this->e($organizer) . '</div>'
            . '  </td>'
            . '</tr></table>'
            . '</td></tr>'

            . "<tr><td style='padding:28px 40px 0;text-align:center;'>"
            . "<a href='" . $this->e($joinUrl) . "' style='display:inline-block;background:linear-gradient(135deg,#667eea,#764ba2);"
            . "color:white;text-decoration:none;padding:14px 36px;border-radius:8px;"
            . "font-size:15px;font-weight:700;letter-spacing:0.3px;'>🔗&nbsp; Rejoindre la réunion</a>"
            . '</td></tr>'

            . "<tr><td style='padding:24px 40px 0;'>"
            . "<table width='100%' cellpadding='0' cellspacing='0'>"

            . "<tr><td style='padding:8px 0;border-bottom:1px solid #f1f5f9;'>"
            . "<span style='font-size:12px;color:#64748b;'>🆔&nbsp; ID de réunion</span>"
            . "<span style='float:right;font-size:12px;font-weight:600;color:#1e293b;font-family:monospace;'>" . $this->e((string) $meetId) . '</span>'
            . '</td></tr>'

            . $passwordRow

            . "<tr><td style='padding:8px 0;'>"
            . "<span style='font-size:12px;color:#64748b;'>🔗&nbsp; Lien de connexion</span><br>"
            . "<a href='" . $this->e($joinUrl) . "' style='font-size:11px;color:#667eea;word-break:break-all;'>" . $this->e($joinUrl) . '</a>'
            . '</td></tr>'

            . '</table></td></tr>'

            . "<tr><td style='padding:20px 40px;'>"
            . "<div style='background:#eff6ff;border-radius:8px;border-left:3px solid #3b82f6;padding:12px 16px;'>"
            . "<p style='margin:0;font-size:12px;color:#1e40af;'>"
            . 'ℹ️&nbsp; Cliquez sur <strong>Rejoindre la réunion</strong> ou copiez le lien dans votre navigateur. '
            . "Assurez-vous d'avoir Zoom installé avant la réunion."
            . '</p></div>'
            . '</td></tr>'

            . "<tr><td style='background:#f8fafc;padding:20px 40px;text-align:center;border-top:1px solid #e2e8f0;'>"
            . "<p style='margin:0;font-size:11px;color:#94a3b8;'>Cet email a été envoyé automatiquement par le Calendrier des Réunions.</p>"
            . '</td></tr>'

            . '</table>'
            . '</td></tr></table>'
            . '</body></html>';
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function formatDate(\DateTimeInterface $dt, string $locale): string
    {
        if (class_exists(\IntlDateFormatter::class)) {
            $fmt = new \IntlDateFormatter(
                $locale,
                \IntlDateFormatter::FULL,
                \IntlDateFormatter::NONE,
                $dt->getTimezone(),
            );

            return $fmt->format($dt) ?: $dt->format('Y-m-d');
        }

        return $dt->format('l d F Y');
    }
}