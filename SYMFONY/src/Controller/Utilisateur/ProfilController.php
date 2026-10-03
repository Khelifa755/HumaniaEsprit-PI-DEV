<?php

namespace App\Controller\Utilisateur;

use App\Entity\Utilisateur;
use App\Service\Utilisateur\UtilisateurService;
use App\Service\Utilisateur\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Replaces Java ProfilController (without face recognition).
 */
#[IsGranted('ROLE_USER')]
#[Route('/profil')]
class ProfilController extends AbstractController
{
    public function __construct(
        private readonly UtilisateurService $utilisateurService,
        private readonly SluggerInterface   $slugger,
        private readonly string             $avatarUploadDir,
    ) {}

    #[Route('', name: 'app_profil', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        /** @var Utilisateur $user */
        $user  = $this->getUser();
        $error = null;

        if ($request->isMethod('POST')) {
            try {
                $nom      = trim((string) $request->request->get('nom', ''));
                $prenom   = trim((string) $request->request->get('prenom', ''));
                $email    = trim((string) $request->request->get('email', ''));
                $username = trim((string) $request->request->get('username', ''));
                $numtel   = trim((string) $request->request->get('numtel', ''));
                $newPwd   = (string) $request->request->get('new_password', '');
                $confirm  = (string) $request->request->get('confirm_password', '');

                if (empty($nom) || empty($prenom)) {
                    throw new \RuntimeException('Nom et prénom sont obligatoires.');
                }
                if (empty($email)) {
                    throw new \RuntimeException("L'email est obligatoire.");
                }
                if ($this->utilisateurService->emailExistePourAutre($user->getId(), $email)) {
                    throw new \RuntimeException("Cet email est déjà utilisé par un autre compte.");
                }
                if ($newPwd !== '') {
                    if (strlen($newPwd) < 6) {
                        throw new \RuntimeException('Le mot de passe doit contenir au moins 6 caractères.');
                    }
                    if ($newPwd !== $confirm) {
                        throw new \RuntimeException('Les mots de passe ne correspondent pas.');
                    }
                }

                // Avatar upload
                $avatarFile = $request->files->get('avatar');
                if ($avatarFile !== null && $avatarFile->getSize() > 0) {
                    // Avoid getMimeType(): it requires php_fileinfo in some environments.
                    $originalName = $avatarFile->getClientOriginalName();
                    $originalExt = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
                    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array($originalExt, $allowedExts, true)) {
                        throw new \RuntimeException('Format d\'image non accepté. Utilisez JPG, PNG, GIF ou WebP.');
                    }
                    $extension = $originalExt === 'jpeg' ? 'jpg' : $originalExt;

                    $safeFilename = $this->slugger->slug(
                        pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME)
                    );
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $extension;

                    try {
                        // Vérifier que le répertoire existe
                        if (!is_dir($this->avatarUploadDir)) {
                            mkdir($this->avatarUploadDir, 0755, true);
                        }

                        $avatarFile->move($this->avatarUploadDir, $newFilename);

                        // Supprimer l'ancienne photo
                        $old = $user->getPdp();
                        if ($old && file_exists($this->avatarUploadDir . '/' . $old)) {
                            @unlink($this->avatarUploadDir . '/' . $old);
                        }

                        // Stocker le nouveau chemin
                        $user->setPdp($newFilename);
                    } catch (FileException $e) {
                        throw new \RuntimeException("Erreur upload avatar : " . $e->getMessage());
                    }
                }

                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setEmail($email);
                $user->setUsername($username);
                $user->setNumtel($numtel);

                $this->utilisateurService->modifierProfil($user, $newPwd ?: null);
                $this->addFlash('success', 'Profil mis à jour avec succès.');
                return $this->redirectToRoute('app_profil');

            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('Utilisateur/profil.html.twig', [
            'utilisateur' => $user,
            'error'       => $error,
        ]);
    }

    #[Route('/face-register', name: 'app_profil_face_register', methods: ['POST'])]
    public function registerFace(Request $request, FaceRecognitionService $faceService, EntityManagerInterface $em): JsonResponse
    {
        // AFTER
        $user = $this->getUser();
        if (!$user instanceof \App\Entity\Utilisateur) {
            return new JsonResponse(['success' => false, 'message' => 'Non autorisé'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $image = $data['image'] ?? null;

        if (!$image) {
            return new JsonResponse(['success' => false, 'message' => 'Image manquante'], 400);
        }

        try {
            // 1. Detect face and get token
            $faceToken = $faceService->detectFace($image);
            if (!$faceToken) {
                return new JsonResponse(['success' => false, 'message' => 'Aucun visage détecté. Veuillez bien cadrer votre visage.'], 400);
            }

            // 2. Add to FaceSet
            $faceService->addFaceToSet($faceToken);

            // 3. Save to user profile
            $user->setDonneesFaciales($faceToken);
            $em->persist($user);
            $em->flush();

            return new JsonResponse(['success' => true, 'message' => 'Visage enregistré avec succès !']);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    #[Route('/face-delete', name: 'app_profil_face_delete', methods: ['POST'])]
    public function deleteFace(Request $request, FaceRecognitionService $faceService, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Utilisateur) {   // ← real runtime check, no contradiction
            throw $this->createAccessDeniedException();
    }

        $token = $user->getDonneesFaciales();
        if ($token) {
            try {
                $faceService->removeFaceFromSet($token);
            } catch (\Exception $e) {
                // we can ignore errors if face doesn't exist remotely or API fails, but still remove locally
                // Or you can flash an error and return if you want strict consistency. We'll just log/ignore for UX.
            }
            $user->setDonneesFaciales(null);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'Votre visage a été supprimé.');
        }

        return $this->redirectToRoute('app_profil');
    }
}