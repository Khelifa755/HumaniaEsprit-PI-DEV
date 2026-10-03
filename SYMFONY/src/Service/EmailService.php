<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EmailService
{
    private string $fromEmail;
    private string $fromName;
    private string $apiKey;
    private HttpClientInterface $httpClient;

    public function __construct(
        string $mailerFrom,
        string $mailerFromName,
        string $brevoApiKey
    ) {
        $this->fromEmail = $mailerFrom;
        $this->fromName = $mailerFromName;
        $this->apiKey = $brevoApiKey;
        $this->httpClient = HttpClient::create();
    }

    /**
     * Envoyer une notification au RH pour une nouvelle demande
     */
    public function envoyerNotificationAbsence(array $data): bool
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'email' => $this->fromEmail,
                        'name' => $this->fromName
                    ],
                    'to' => [
                        [
                            'email' => $data['destinataire_email'],
                            'name' => 'RH Humania'
                        ]
                    ],
                    'subject' => '⏰ Nouvelle demande d\'autorisation d\'absence',
                    'htmlContent' => $this->getTemplateAbsence($data),
                ],
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 201 || $statusCode === 202) {
                error_log('✅ Email envoyé via API Brevo à ' . $data['destinataire_email']);
                return true;
            }
            
            $content = $response->getContent(false);
            error_log('❌ API Brevo erreur: ' . $content);
            return false;
            
        } catch (\Throwable $e) {
            error_log('❌ Exception API Brevo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification au manager pour validation
     */
    public function envoyerNotificationManager(array $data): bool
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'email' => $this->fromEmail,
                        'name' => $this->fromName
                    ],
                    'to' => [
                        [
                            'email' => $data['manager_email'],
                            'name' => 'Manager Humania'
                        ]
                    ],
                    'subject' => '⏰ Nouvelle demande d\'autorisation à valider',
                    'htmlContent' => $this->getTemplateNotificationManager($data),
                ],
            ]);
            
            $statusCode = $response->getStatusCode();
            
            if ($statusCode === 201 || $statusCode === 202) {
                error_log('✅ Notification manager envoyée à ' . $data['manager_email']);
                return true;
            }
            
            return false;
            
        } catch (\Throwable $e) {
            error_log('❌ Exception notification manager: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoyer une notification à l'employé après validation
     */
    public function envoyerNotificationValidation(array $data): bool
    {
        try {
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'json' => [
                    'sender' => [
                        'email' => $this->fromEmail,
                        'name' => $this->fromName
                    ],
                    'to' => [
                        [
                            'email' => $data['employe_email'],
                            'name' => $data['employe_nom']
                        ]
                    ],
                    'subject' => $data['statut'] === 'Approuvé' ? '✅ Votre demande a été approuvée' : '❌ Votre demande a été refusée',
                    'htmlContent' => $this->getTemplateValidation($data),
                ],
            ]);
            
            $statusCode = $response->getStatusCode();
            return $statusCode === 201 || $statusCode === 202;
            
        } catch (\Throwable $e) {
            error_log('❌ Exception notification validation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Template email pour le manager
     */
    private function getTemplateNotificationManager(array $data): string
    {
        $employe = htmlspecialchars($data['employe_nom'] ?? 'Employé');
        $date = htmlspecialchars($data['date'] ?? 'N/A');
        $type = htmlspecialchars($data['type'] ?? 'Absence');
        $motif = htmlspecialchars($data['motif'] ?? 'Non précisé');
        $lienApprouver = $data['lien_approuver'] ?? '#';
        $lienRefuser = $data['lien_refuser'] ?? '#';

        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouvelle demande - Humania</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; padding: 20px; }
        .container { max-width: 550px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #f59e0b, #d97706); padding: 24px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 22px; }
        .content { padding: 28px; }
        .info-row { margin-bottom: 16px; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .label { font-weight: 700; color: #475569; width: 100px; display: inline-block; }
        .value { color: #1e293b; }
        .badge-pending { background: #fef3c7; color: #b45309; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; display: inline-block; }
        .actions { margin-top: 28px; display: flex; gap: 16px; justify-content: center; }
        .btn-approve { background: #10b981; color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .btn-reject { background: #ef4444; color: white; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Nouvelle demande</h1>
            <p>Une autorisation d\'absence demande votre validation</p>
        </div>
        <div class="content">
            <div class="info-row">
                <span class="label">👤 Employé :</span>
                <span class="value">' . $employe . '</span>
            </div>
            <div class="info-row">
                <span class="label">📅 Date :</span>
                <span class="value">' . $date . '</span>
            </div>
            <div class="info-row">
                <span class="label">📋 Type :</span>
                <span class="value">' . $type . '</span>
            </div>
            <div class="info-row">
                <span class="label">💬 Motif :</span>
                <span class="value">' . $motif . '</span>
            </div>
            <div class="info-row">
                <span class="label">📊 Statut :</span>
                <span class="badge-pending">En attente</span>
            </div>
            <div class="actions">
                <a href="' . $lienApprouver . '" class="btn-approve">✅ Approuver</a>
                <a href="' . $lienRefuser . '" class="btn-reject">❌ Refuser</a>
            </div>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par Humania RH</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template email pour l'employé après validation
     */
    private function getTemplateValidation(array $data): string
    {
        $statut = htmlspecialchars($data['statut'] ?? 'En attente');
        $badgeColor = $statut === 'Approuvé' ? '#10b981' : '#ef4444';
        $message = $statut === 'Approuvé' 
            ? 'Félicitations ! Votre demande a été acceptée.'
            : 'Nous sommes désolés, votre demande a été refusée.';

        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réponse à votre demande - Humania</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; padding: 20px; }
        .container { max-width: 550px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); padding: 24px; text-align: center; color: white; }
        .content { padding: 28px; text-align: center; }
        .badge { display: inline-block; padding: 8px 24px; background: ' . $badgeColor . '; color: white; border-radius: 30px; font-size: 14px; font-weight: bold; margin: 20px 0; }
        .footer { background: #f8fafc; padding: 16px; text-align: center; font-size: 11px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . ($statut === 'Approuvé' ? '✅ Demande approuvée' : '❌ Demande refusée') . '</h1>
        </div>
        <div class="content">
            <p>' . $message . '</p>
            <div class="badge">' . $statut . '</div>
            <p>Pour plus d\'informations, connectez-vous à votre espace Humania.</p>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par Humania RH</p>
        </div>
    </div>
</body>
</html>';
    }

    /**
     * Template email pour le RH (existant)
     */
    private function getTemplateAbsence(array $data): string
    {
        $employe    = htmlspecialchars($data['employe_nom']  ?? 'Employé');
        $date       = htmlspecialchars($data['date']         ?? 'N/A');
        $heureDebut = htmlspecialchars($data['heure_debut']  ?? 'N/A');
        $heureFin   = htmlspecialchars($data['heure_fin']    ?? 'N/A');
        $duree      = htmlspecialchars($data['duree']        ?? 'N/A');
        $type       = htmlspecialchars($data['type']         ?? 'Absence');
        $motif      = htmlspecialchars($data['motif']        ?? 'Non précisé');
        $statut     = htmlspecialchars($data['statut']       ?? 'En attente');

        $badgeColor = match(strtolower($statut)) {
            'approuvé', 'justifiée' => '#10b981',
            'refusé', 'injustifiée' => '#ef4444',
            default => '#f59e0b',
        };

        return '
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demande d\'autorisation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
        .content { background: #f9fafb; padding: 30px; border-radius: 0 0 10px 10px; }
        .info-row { margin-bottom: 15px; padding: 10px; background: white; border-radius: 8px; }
        .label { font-weight: bold; color: #4b5563; display: inline-block; width: 120px; }
        .value { color: #1f2937; }
        .badge { display: inline-block; padding: 5px 15px; background: ' . $badgeColor . '; color: white; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; }
        hr { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 style="margin: 0;">Nouvelle demande d\'autorisation</h1>
            <p style="margin: 10px 0 0;">Une demande d\'absence vient d\'être enregistrée</p>
        </div>
        <div class="content">
            <div class="info-row">
                <span class="label">👤 Employé :</span>
                <span class="value">' . $employe . '</span>
            </div>
            <div class="info-row">
                <span class="label">📅 Date :</span>
                <span class="value">' . $date . '</span>
            </div>
            <div class="info-row">
                <span class="label">⏰ Horaires :</span>
                <span class="value">' . $heureDebut . ' → ' . $heureFin . '</span>
            </div>
            <div class="info-row">
                <span class="label">⏱️ Durée :</span>
                <span class="value">' . $duree . '</span>
            </div>
            <div class="info-row">
                <span class="label">📋 Type :</span>
                <span class="value">' . $type . '</span>
            </div>
            <div class="info-row">
                <span class="label">💬 Motif :</span>
                <span class="value">' . $motif . '</span>
            </div>
            <div class="info-row">
                <span class="label">📊 Statut :</span>
                <span class="badge">' . $statut . '</span>
            </div>
            <hr>
            <div style="text-align: center;">
                <a href="http://localhost:8000/absence" style="display: inline-block; background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none; padding: 12px 30px; border-radius: 8px; font-weight: bold;">Voir dans Humania</a>
            </div>
        </div>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement par <strong>Humania RH</strong></p>
            <p>Merci de ne pas répondre à cet email.</p>
        </div>
    </div>
</body>
</html>';
    }
}