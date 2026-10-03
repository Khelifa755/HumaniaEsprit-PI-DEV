<?php

namespace App\Service\Utilisateur;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Mail service for sending emails (OTP, credentials, etc.)
 * Replaces Java MailService.
 */
class MailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * Send OTP code for password reset
     */
    public function envoyerOtp(string $email, string $otp): void
    {
        $emailMessage = (new Email())
            ->from($_ENV['MAILER_FROM_ADDRESS'] ?? 'aminezaaraoui95@gmail.com')
            ->to($email)
            ->subject('Code de réinitialisation de mot de passe - Humania')
            ->html($this->renderOtpTemplate($otp));

        try {
            $this->mailer->send($emailMessage);
            $this->logger->info("OTP email sent to $email");
        } catch (\Throwable $e) {
            $this->logger->error("Failed to send OTP email to $email: " . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException("Erreur lors de l'envoi du code OTP: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Send login credentials to newly created employee
     */
    public function envoyerCredentials(string $email, string $username, string $password): void
    {
        $emailMessage = (new Email())
            ->from($_ENV['MAILER_FROM_ADDRESS'] ?? 'aminezaaraoui95@gmail.com')
            ->to($email)
            ->subject('Vos identifiants de connexion - Humania')
            ->html($this->renderCredentialsTemplate($username, $password, $email));

        try {
            $this->mailer->send($emailMessage);
            $this->logger->info("Credentials email sent to $email for user $username");
        } catch (\Throwable $e) {
            $this->logger->error("Failed to send credentials email to $email: " . $e->getMessage(), ['exception' => $e]);
            throw new \RuntimeException("Erreur lors de l'envoi des identifiants: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Render OTP email template
     */
    private function renderOtpTemplate(string $otp): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; }
                .header { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 15px; }
                .content { color: #666; line-height: 1.6; margin: 20px 0; }
                .code { font-size: 24px; font-weight: bold; color: #0066cc; letter-spacing: 3px; text-align: center; padding: 15px; background-color: #f0f0f0; border-radius: 5px; }
                .footer { color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Réinitialisation de mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe. Veuillez utiliser le code suivant pour procéder :</p>
                    <div class="code">$otp</div>
                    <p>Ce code expire dans 10 minutes.</p>
                    <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
                </div>
                <div class="footer">
                    <p>© 2026 Humania. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Render credentials email template
     */
    private function renderCredentialsTemplate(string $username, string $password, string $email): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; background-color: #f5f5f5; }
                .container { max-width: 600px; margin: 20px auto; background-color: white; padding: 20px; border-radius: 8px; }
                .header { color: #333; border-bottom: 2px solid #0066cc; padding-bottom: 15px; }
                .content { color: #666; line-height: 1.6; margin: 20px 0; }
                .credentials { background-color: #f0f0f0; border-left: 4px solid #0066cc; padding: 15px; margin: 15px 0; }
                .credentials p { margin: 10px 0; }
                .label { font-weight: bold; color: #333; }
                .value { color: #0066cc; font-family: monospace; }
                .warning { background-color: #fff3cd; border: 1px solid #ffc107; padding: 12px; border-radius: 4px; margin: 15px 0; }
                .footer { color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 15px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Bienvenue chez Humania!</h2>
                </div>
                <div class="content">
                    <p>Bonjour,</p>
                    <p>Votre compte a été créé avec succès. Voici vos identifiants de connexion :</p>
                    <div class="credentials">
                        <p><span class="label">Email :</span> <span class="value">$email</span></p>
                        <p><span class="label">Identifiant :</span> <span class="value">$username</span></p>
                        <p><span class="label">Mot de passe :</span> <span class="value">$password</span></p>
                    </div>
                    <div class="warning">
                        <strong>⚠️ Important :</strong> Conservez ces identifiants en sécurité. Changez votre mot de passe dès votre première connexion.
                    </div>
                    <p><a href="https://humania.tn/login" style="display: inline-block; background-color: #0066cc; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">Se connecter</a></p>
                </div>
                <div class="footer">
                    <p>© 2026 Humania. Tous droits réservés.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
