<?php

namespace App\Controller\Utilisateur;

use App\Entity\Candidature_externe;
use App\Entity\Poste_externe;
use App\Repository\RECRUTEMENT\Poste_externeRepository;
use App\Repository\Utilisateur\UtilisateurRepository;
use App\Service\Utilisateur\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\String\Slugger\SluggerInterface;

class SecurityController extends AbstractController
{
    private const MAX_ATTEMPTS = 3;

    #[Route('/', name: 'app_redirect_to_login', methods: ['GET'])]
    public function home(): RedirectResponse
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils, Poste_externeRepository $posteRepo, Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();
        $offres = $posteRepo->findBy([], ['datePublication' => 'DESC']);

        $totalOffres = count($offres);
        $postesOuverts = count(array_filter($offres, static fn(Poste_externe $o) => $o->getStatut() === 'Ouvert'));

        $tabCounts = [
            'all' => $totalOffres,
            'ouvert' => $postesOuverts,
            'cdi' => count(array_filter($offres, static fn(Poste_externe $o) => strtoupper((string) $o->getTypeContrat()) === 'CDI')),
            'cdd' => count(array_filter($offres, static fn(Poste_externe $o) => strtoupper((string) $o->getTypeContrat()) === 'CDD')),
            'stage' => count(array_filter($offres, static fn(Poste_externe $o) => strtoupper((string) $o->getTypeContrat()) === 'STAGE')),
        ];

        $offresPayload = array_map(static function (Poste_externe $offre): array {
            return [
                'id' => $offre->getId(),
                'titre' => $offre->getTitre(),
                'typeContrat' => $offre->getTypeContrat(),
                'statut' => $offre->getStatut(),
                'dateCloture' => $offre->getDateCloture()?->format('d/m/Y'),
                'experienceRequise' => $offre->getExperienceRequise(),
                'niveauEtudeRequis' => $offre->getNiveauEtudeRequis(),
                'salaire' => $offre->getSalaire(),
                'nombreEmploye' => $offre->getNombreEmploye(),
                'competencesRequises' => $offre->getCompetencesRequises(),
                'priorite' => $offre->getPriorite(),
            ];
        }, $offres);

        return $this->render('Utilisateur/security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'login_attempts' => (int) $session->get('login_attempts', 0),
            'max_attempts' => self::MAX_ATTEMPTS,
            'lockout_remaining' => max(0, (int) $session->get('lockout_until', 0) - time()),
            'recaptcha_site_key' => (string) $this->getParameter('recaptcha_site_key'),
            'offres' => $offresPayload,
            'tabCounts' => $tabCounts,
            'totalOffres' => $totalOffres,
            'postesOuverts' => $postesOuverts,
            'tauxSatisfaction' => 94,
            'delaiMoyen' => 12,
            'totalCandidats' => 1200,
            'totalRecrutements' => 286,
            'totalEntreprises' => 74,
            'noteSatisfaction' => 4.8,
            'totalAvis' => 320,
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode est interceptée par le firewall.');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password', methods: ['GET'])]
    public function forgotPassword(): Response
    {
        return $this->render('Utilisateur/security/forgot_password.html.twig');
    }

    #[Route('/connect/google', name: 'connect_google', methods: ['GET'])]
    public function connectGoogle(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry->getClient('google')->redirect(['email', 'profile']);
    }

    #[Route('/connect/google/check', name: 'connect_google_check', methods: ['GET'])]
    public function connectGoogleCheck(): void
    {
        throw new \LogicException('Cette route est gérée par le firewall GoogleAuthenticator.');
    }

    #[Route('/login/face', name: 'app_login_face', methods: ['POST'])]
    public function loginWithFace(Request $request, FaceRecognitionService $faceService, UtilisateurRepository $userRepo): JsonResponse
    {
        $payload = json_decode((string) $request->getContent(), true);
        $image = is_array($payload) ? ($payload['image'] ?? null) : null;

        if (!is_string($image) || $image === '') {
            return $this->json(['success' => false, 'message' => 'Image manquante.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $faceToken = $faceService->searchFace($image);
            if (!$faceToken) {
                return $this->json(['success' => false, 'message' => 'Visage non reconnu.'], Response::HTTP_UNAUTHORIZED);
            }

            $user = $userRepo->findOneBy(['donneesFaciales' => $faceToken]);
            if (!$user) {
                return $this->json(['success' => false, 'message' => 'Aucun compte associé à ce visage.'], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([
                'success' => true,
                'redirect' => $this->generateUrl('app_login'),
                'message' => 'Visage reconnu.',
            ]);
        } catch (\Throwable $e) {
            return $this->json(['success' => false, 'message' => 'Erreur de reconnaissance faciale.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/candidature-externe/submit', name: 'app_candidature_submit', methods: ['POST'])]
    public function submitCandidature(Request $request, EntityManagerInterface $em, SluggerInterface $slugger): JsonResponse
    {
        $data = $request->request->all();
        $posteId = $data['poste_externe_id'] ?? null;

        if (!$posteId) {
            return $this->json(['success' => false, 'message' => 'ID de l\'offre manquant'], 400);
        }

        $poste = $em->getRepository(Poste_externe::class)->find((int)$posteId);
        if (!$poste || $poste->getStatut() !== 'Ouvert') {
            return $this->json(['success' => false, 'message' => 'Cette offre n\'est plus disponible.'], 400);
        }

        $candidature = new Candidature_externe();

        // === RELATION PRINCIPALE ===
        $candidature->setPosteExterneId($poste->getId());           // ← CORRECTION ICI

        $candidature->setPrenom(trim($data['prenom'] ?? ''));
        $candidature->setNom(trim($data['nom'] ?? ''));
        $candidature->setEmail(trim($data['email'] ?? ''));
        $candidature->setTelephone(trim($data['telephone'] ?? ''));
        $candidature->setVille(trim($data['ville'] ?? ''));
        $candidature->setExperience(trim($data['experience'] ?? ''));
        $candidature->setFormation(trim($data['formation'] ?? ''));
        $candidature->setLinkedin(trim($data['linkedin'] ?? ''));
        $candidature->setMotivation(trim($data['motivation'] ?? ''));           // texte
        $candidature->setDisponibilite(trim($data['disponibilite'] ?? ''));
        $candidature->setPretentionSalariale(!empty($data['salaire_pretendu']) ? (int)$data['salaire_pretendu'] : null);

        // Consentement
        $candidature->setConsentement(filter_var($data['rgpd'] ?? false, FILTER_VALIDATE_BOOLEAN));

        // === CV Upload ===
        if ($cvFile = $request->files->get('cv')) {
            if ($cvFile->isValid()) {
                $safeName = $slugger->slug($cvFile->getClientOriginalName());
                $filename = $safeName . '-' . bin2hex(random_bytes(8)) . '.' . $cvFile->guessExtension();

                try {
                    $cvFile->move($this->getParameter('cv_upload_directory'), $filename);
                    $candidature->setCvUrl('/uploads/cvs/' . $filename);
                } catch (\Exception $e) {
                    // tu peux logger ici
                }
            }
        }

        try {
            $em->persist($candidature);
            $em->flush();

            return $this->json([
                'success' => true,
                'message' => 'Candidature envoyée avec succès !'
            ]);
        } catch (\Throwable $e) {
            // Toujours retourner du JSON — ne jamais appeler dd() dans un endpoint AJAX
            return $this->json([
                'success' => false,
                'message' => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }

    #[Route('/api/offres/filter', name: 'app_offres_filter', methods: ['GET'])]
    public function filterOffres(Request $request, Poste_externeRepository $posteRepo): JsonResponse
    {
        // ... (inchangé)
        $filter = (string) $request->query->get('filter', 'all');
        $qb = $posteRepo->createQueryBuilder('p');

        switch ($filter) {
            case 'ouvert':
                $qb->where('p.statut = :statut')->setParameter('statut', 'Ouvert');
                break;
            case 'cdi':
                $qb->where('p.typeContrat = :contrat')->setParameter('contrat', 'CDI');
                break;
            case 'cdd':
                $qb->where('p.typeContrat = :contrat')->setParameter('contrat', 'CDD');
                break;
            case 'stage':
                $qb->where('p.typeContrat = :contrat')->setParameter('contrat', 'STAGE');
                break;
        }

        $qb->orderBy('p.priorite', 'ASC')
            ->addOrderBy('p.datePublication', 'DESC');

        $offres = $qb->getQuery()->getResult();
        $data = [];

        foreach ($offres as $offre) {
            $data[] = [
                'id' => $offre->getId(),
                'titre' => $offre->getTitre(),
                'typeContrat' => $offre->getTypeContrat(),
                'statut' => $offre->getStatut(),
                'dateCloture' => $offre->getDateCloture()?->format('d/m/Y'),
                'experienceRequise' => $offre->getExperienceRequise(),
                'niveauEtudeRequis' => $offre->getNiveauEtudeRequis(),
                'salaire' => $offre->getSalaire(),
                'nombreEmploye' => $offre->getNombreEmploye(),
                'competencesRequises' => $offre->getCompetencesRequises(),
                'priorite' => $offre->getPriorite(),
            ];
        }

        return $this->json($data);
    }
}
