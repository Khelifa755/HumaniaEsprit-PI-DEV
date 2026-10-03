<?php

namespace App\Service\Plannification;

use App\Entity\Reunion;

final class ReunionCalendarMapper
{
    /**
     * Détecte le type de réunion depuis le préfixe de la description.
     * Convention : [EVENT], [ONBOARDING], [OFFBOARDING], [RESERVATION] → sinon MEETING.
     */
    public static function detectTypeKey(?string $description): string
    {
        $desc = strtoupper(trim((string) ($description ?? '')));

        if (str_starts_with($desc, '[EVENT]'))       return 'EVENT';
        if (str_starts_with($desc, '[ONBOARDING]'))  return 'ONBOARDING';
        if (str_starts_with($desc, '[OFFBOARDING]')) return 'OFFBOARDING';
        if (str_starts_with($desc, '[RESERVATION]')) return 'RESERVATION';

        return 'MEETING';
    }

    public static function typeLabel(string $typeKey): string
    {
        return match ($typeKey) {
            'EVENT'       => 'Événement',
            'RESERVATION' => 'Réservation',
            'ONBOARDING'  => 'Onboarding',
            'OFFBOARDING' => 'Offboarding',
            default       => 'Réunion',
        };
    }

    public static function typeIcon(string $typeKey): string
    {
        return match ($typeKey) {
            'EVENT'       => '🎉',
            'RESERVATION' => '🪑',
            'ONBOARDING'  => '🟢',
            'OFFBOARDING' => '🔴',
            default       => '📅',
        };
    }

    public static function typeColor(string $typeKey): string
    {
        return match ($typeKey) {
            'EVENT'       => '#8b5cf6',   // violet
            'RESERVATION' => '#3b82f6',   // bleu
            'ONBOARDING'  => '#22c55e',   // vert
            'OFFBOARDING' => '#f97316',   // orange
            default       => '#ef4444',   // rouge (réunion)
        };
    }

    public static function eventColor(Reunion $r): string
    {
        if (!$r->getStatut()) {
            return '#94a3b8'; // gris — annulée
        }

        if ($r->getEnLigne()) {
            return '#ef4444'; // rouge — en ligne (Zoom)
        }

        return self::typeColor(self::detectTypeKey($r->getDescription()));
    }

    /**
     * Propriétés étendues consommées par FullCalendar et le popover.
     * IMPORTANT : zoomJoinUrl est inclus ici pour que le popover puisse
     *             afficher le lien Zoom sans appel supplémentaire.
     *
     * @return array<string, mixed>
     */
    public static function extendedProps(Reunion $r): array
    {
        $typeKey = self::detectTypeKey($r->getDescription());

        return [
            'description'      => $r->getDescription(),
            'enLigne'          => (bool) $r->getEnLigne(),
            'statut'           => (bool) $r->getStatut(),
            'salle'            => $r->getIdSalle()?->getNom(),
            'salleId'          => $r->getIdSalle()?->getId(),
            'organisateur'     => $r->getNomOrganisateur(),
            'emailOrganisateur'=> $r->getEmailOrganisateur(),
            'typeKey'          => $typeKey,
            'typeLabel'        => self::typeLabel($typeKey),
            'typeIcon'         => self::typeIcon($typeKey),
            // Zoom — peut être vide si la réunion n'est pas en ligne
            'zoomJoinUrl'      => $r->getZoom_join_url() ?: null,
            'zoomMeetingId'    => $r->getZoom_meeting_id() !== '0' ? $r->getZoom_meeting_id() : null,
        ];
    }
}
