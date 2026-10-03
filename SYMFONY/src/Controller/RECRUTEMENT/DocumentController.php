<?php

namespace App\Controller\RECRUTEMENT;

use App\Entity\Document;
use App\Form\RECRUTEMENT\DocumentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/document')]
final class DocumentController extends AbstractController
{
    #[Route(name: 'app_document_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $documents = $entityManager
            ->getRepository(Document::class)
            ->findAll();

        return $this->render('RECRUTEMENT/document/index.html.twig', [
            'documents' => $documents,
        ]);
    }

    #[Route('/new', name: 'app_document_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $document = new Document();

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('document_upload', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
            }

            // Lecture des données brutes du formulaire modal
            $data = $request->request->all('document');

            $name = isset($data['name']) ? trim((string) $data['name']) : '';
            if ($name !== '') {
                $document->setName($name);
            }

            /** @var array<string, mixed> $fileBag */
            $fileBag = (array) $request->files->get('document', []);
            $file = $fileBag['file'] ?? null;

            if ($file instanceof UploadedFile) {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = (string) $slugger->slug($originalName);
                $ext = strtolower((string) $file->guessExtension());
                if ($ext === '') {
                    $ext = strtolower((string) $file->getClientOriginalExtension());
                }
                if ($ext === '') {
                    $ext = 'bin';
                }

                $fileName = $safeName . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
                $targetDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'documents';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0775, true);
                }

                $file->move($targetDir, $fileName);

                if ($document->getName() === '') {
                    $document->setName($originalName . '.' . $ext);
                }

                $document->setPath('/uploads/documents/' . $fileName);
                $document->setType(strtoupper($ext));
            } else {
                // Fallback: allow manually entering a URL/path if no file was chosen
                $path = isset($data['path']) ? trim((string) $data['path']) : '';
                $type = isset($data['type']) ? trim((string) $data['type']) : '';
                if ($path !== '') {
                    $document->setPath($path);
                }
                if ($type !== '') {
                    $document->setType($type);
                }
            }

            // Minimal validation for required fields
            if ($document->getName() === '' || $document->getPath() === '' || $document->getType() === '') {
                $this->addFlash('danger', 'Veuillez sélectionner un fichier et renseigner les informations requises.');
                return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
            }

            $entityManager->persist($document);
            $entityManager->flush();
            $this->addFlash('success', 'Document téléversé avec succès.');

            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        // Formulaire Symfony classique (fallback page /new)
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($document);
            $entityManager->flush();

            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/document/new.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_document_show', methods: ['GET'])]
    public function show(Document $document): Response
    {
        return $this->render('RECRUTEMENT/document/show.html.twig', [
            'document' => $document,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_document_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Document mis à jour.');

            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('RECRUTEMENT/document/edit.html.twig', [
            'document' => $document,
            'form'     => $form,
        ]);
    }

    #[Route('/{id}/sign', name: 'app_document_sign', methods: ['POST'])]
    public function sign(Request $request, Document $document): Response
    {
        $id = $document->getId();
        if ($id === null || !$this->isCsrfTokenValid('document_sign_' . $id, (string) $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        $prenom = trim((string) $request->request->get('signataire_prenom', ''));
        $nom = trim((string) $request->request->get('signataire_nom', ''));
        $email = trim((string) $request->request->get('signataire_email', ''));

        if ($prenom === '' || $nom === '' || $email === '') {
            $this->addFlash('error', 'Veuillez renseigner le prénom, le nom et l\'email du signataire.');
            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Adresse email invalide.');
            return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
        }

        // TODO: appeler l’API SignNow avec le document et les coordonnées du signataire.
        $this->addFlash('success', sprintf(
            'Demande de signature pour « %s » enregistrée. %s %s recevra un email SignNow avec son lien de signature.',
            $document->getName(),
            $prenom,
            $nom
        ));

        return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_document_delete', methods: ['POST'])]
    public function delete(Request $request, Document $document, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $document->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($document);
            $entityManager->flush();
            $this->addFlash('success', 'Document supprimé.');
        }

        return $this->redirectToRoute('app_document_index', [], Response::HTTP_SEE_OTHER);
    }
}
