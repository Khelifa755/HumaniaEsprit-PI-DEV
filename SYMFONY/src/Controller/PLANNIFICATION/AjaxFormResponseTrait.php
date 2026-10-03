<?php

namespace App\Controller\PLANNIFICATION;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Fournit les helpers AJAX utilisés par ReunionController.
 * À inclure via : use AjaxFormResponseTrait;
 */
trait AjaxFormResponseTrait
{
    /**
     * Retourne true si la requête attend une réponse JSON (appel AJAX depuis reunion_modal.js).
     */
    private function wantsModalJson(Request $request): bool
    {
        return $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');
    }

    /**
     * Réponse JSON de succès : redirige vers la route indiquée.
     *
     * @param array<string, mixed> $routeParams
     * @param array<string, mixed> $extra        Données supplémentaires fusionnées dans la réponse.
     * @param array<string, mixed> $zoomFragment  Fragment Zoom (joinUrl, warning…).
     */
    private function jsonModalSuccess(
        string $routeName,
        array  $routeParams  = [],
        array  $extra        = [],
        array  $zoomFragment = [],
    ): JsonResponse {
        return new JsonResponse([
            'success'  => true,
            'redirect' => $this->generateUrl($routeName, $routeParams),
            ...$extra,
            ...$zoomFragment,
        ]);
    }

    /**
     * Réponse JSON d'échec : renvoie les erreurs du formulaire.
     */
    private function jsonModalInvalid(FormInterface $form): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'errors'  => $this->collectFormErrors($form),
        ]);
    }

    /**
     * Parcourt récursivement le formulaire et collecte toutes les erreurs.
     *
     * @return array<string, string[]>
     */
    private function collectFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            $errors['_global'][] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            foreach ($child->getErrors(true) as $error) {
                $errors[$child->getName()][] = $error->getMessage();
            }
        }

        return $errors;
    }
}
