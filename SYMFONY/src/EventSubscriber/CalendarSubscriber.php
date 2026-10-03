<?php

namespace App\EventSubscriber;

use App\Entity\Reunion;
use CalendarBundle\CalendarEvents;
use CalendarBundle\Entity\Event;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\DBAL\Types\Types;                      // ← ADD THIS
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Plannification\ReunionCalendarMapper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\SecurityBundle\Security;

class CalendarSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RouterInterface $router,
        private Security $security,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendarEvent): void
    {
        $start   = $calendarEvent->getStart();
        $end     = $calendarEvent->getEnd();
        $user = $this->security->getUser();
        $userEmail = (is_object($user) && method_exists($user, 'getEmail'))
            ? (string) $user->getEmail()
            : null;

        if ($userEmail === null || $userEmail === '') {
            return;
        }

        $reunions = $this->entityManager
            ->getRepository(Reunion::class)
            ->createQueryBuilder('r')
            ->where('r.dateHeureDebut < :end')
            ->andWhere('r.dateHeureFin > :start')
            ->andWhere('r.emailOrganisateur = :email')
            ->setParameter('start', $start, Types::DATETIME_MUTABLE)   // ← FIXED
            ->setParameter('end',   $end,   Types::DATETIME_MUTABLE)   // ← FIXED
            ->setParameter('email', $userEmail)
            ->orderBy('r.dateHeureDebut', 'ASC')
            ->getQuery()
            ->getResult();

        /** @var Reunion $reunion */
        foreach ($reunions as $reunion) {
            $event = new Event(
                $reunion->getTitre(),
                \DateTime::createFromInterface($reunion->getDateHeureDebut()),
                \DateTime::createFromInterface($reunion->getDateHeureFin()),
            );

            $baseOptions = [
                'id'          => (string) $reunion->getId(),
                ...ReunionCalendarMapper::extendedProps($reunion),
                'url'         => $this->router->generate(   // ← MOVED HERE
                    'app_reunion_show',
                    ['id' => $reunion->getId()]
                ),
            ];

            $color = ReunionCalendarMapper::eventColor($reunion);
            $typeKey = ReunionCalendarMapper::detectTypeKey($reunion->getDescription());

            $classNames = ['fc-reunion'];
            if (!$reunion->getStatut()) {
                $classNames[] = 'fc-reunion-cancelled';
            } else {
                $classNames[] = 'fc-type-'.strtolower($typeKey);
                if ($reunion->getEnLigne()) {
                    $classNames[] = 'fc-reunion-online';
                }
            }

            $event->setOptions([
                ...$baseOptions,
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'classNames'      => $classNames,
            ]);

            $calendarEvent->addEvent($event);
        }
    }
}