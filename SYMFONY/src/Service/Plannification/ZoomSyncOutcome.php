<?php

namespace App\Service\Plannification;

/**
 * Résultat d'une opération de synchronisation Zoom.
 * Utilisé pour transmettre l'outcome au contrôleur sans coupler la logique métier à HTTP.
 */
final class ZoomSyncOutcome
{
    private function __construct(
        public readonly bool    $success,
        public readonly bool    $skipped,
        public readonly ?string $joinUrl,
        public readonly ?string $userMessage,
    ) {}

    public static function ok(?string $joinUrl): self
    {
        return new self(true, false, $joinUrl, null);
    }

    public static function skipped(): self
    {
        return new self(true, true, null, null);
    }

    public static function failed(string $userMessage): self
    {
        return new self(false, false, null, $userMessage);
    }

    /**
     * Fragment JSON à fusionner dans la réponse AJAX de succès.
     *
     * @return array<string, mixed>
     */
    public function toJsonFragment(): array
    {
        $fragment = [];

        if (!$this->success && $this->userMessage) {
            $fragment['zoomWarning'] = $this->userMessage;
        }

        if ($this->joinUrl) {
            $fragment['zoomJoinUrl'] = $this->joinUrl;
        }

        return $fragment;
    }
}
