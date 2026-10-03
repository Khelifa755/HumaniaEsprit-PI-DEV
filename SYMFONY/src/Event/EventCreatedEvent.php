<?php

namespace App\Event;

use App\Entity\Evenement;

final class EventCreatedEvent
{
    public function __construct(
        private readonly Evenement $event,
    ) {}

    public function getEvent(): Evenement
    {
        return $this->event;
    }
}

