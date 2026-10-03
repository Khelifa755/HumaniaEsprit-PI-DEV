<?php

namespace App\Controller\Utilisateur;

use App\Entity\Utilisateur;
use App\Service\Utilisateur\MfaService;
use App\Service\Utilisateur\UtilisateurService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/profil/mfa')]
class MfaController extends AbstractController
{
    public function __construct(
        private readonly MfaService $mfaService,
        private readonly UtilisateurService $utilisateurService,
    ) {}

    #[Route('/setup', name: 'app_mfa_setup', methods: ['GET', 'POST'])]
    public function setup(Request $request): Response
    {
        /** @var Utilisateur $user */
        $user = $this->getUser();
        $session = $request->getSession();

        if (!$session->get('mfa_pending_secret')) {
            $session->set('mfa_pending_secret', $this->mfaService->generateSecret());
        }
        $pendingSecret = $session->get('mfa_pending_secret');

        if ($request->isMethod('POST')) {
            $code = trim((string) $request->request->get('code', ''));
            if ($this->mfaService->verifyCode($pendingSecret, $code)) {
                $this->utilisateurService->activerMfa($user, $pendingSecret);
                $session->remove('mfa_pending_secret');
                $this->addFlash('success', 'MFA activée avec succès.');
                return $this->redirectToRoute('app_profil');
            }
            $this->addFlash('error', 'Code invalide. Vérifiez le code dans votre application d\'authentification.');
        }

        // Generate OTP URI for QR code
        $otpUri = $this->mfaService->getOtpAuthUri($pendingSecret, $user->getEmail());

        return $this->render('Utilisateur/mfa_setup.html.twig', [
            'secret'   => $pendingSecret,
            'otp_uri'  => $otpUri,
        ]);
    }

    #[Route('/qr-code', name: 'app_mfa_qrcode')]
    public function getQrCode(Request $request): Response
    {
        $otpUri = $request->query->get('uri', '');
        if (!$otpUri) {
            return new Response('Missing URI', 400);
        }

        // Generate QR code using a simple library approach
        // Use a public API for QR code generation (no auth needed)
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($otpUri);
        
        try {
            $context = stream_context_create([
                'http' => ['timeout' => 5]
            ]);
            $qrImage = @file_get_contents($qrUrl, false, $context);
            
            if ($qrImage === false) {
                throw new \Exception('Failed to fetch QR code');
            }

            return new Response($qrImage, 200, ['Content-Type' => 'image/png']);
        } catch (\Exception $e) {
            // Fallback: return a simple message
            return new Response('QR Code generation failed', 500);
        }
    }

    #[Route('/disable', name: 'app_mfa_disable', methods: ['POST'])]
public function disable(): Response
{
    /** @var \App\Entity\Utilisateur $user */
    $user = $this->getUser();
    $this->utilisateurService->desactiverMfa($user);
    return $this->redirectToRoute('app_profil');
}
}