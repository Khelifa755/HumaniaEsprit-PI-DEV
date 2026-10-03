<?php

namespace App\Form\PLANNIFICATION;

use App\Entity\Espaces;
use App\Entity\Reunion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ReunionModalType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'reunion';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['maxlength' => 255, 'autocomplete' => 'off'],
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire.')],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Objectif…',
                ],
            ])
            ->add('debut_date', DateType::class, [
                'label' => false,
                'mapped' => false,
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank(message: 'La date de début est obligatoire.')],
            ])
            ->add('debut_time', TimeType::class, [
                'label' => false,
                'mapped' => false,
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'string',
                // HTML5 + PRE_SET_DATA utilisent « H:i » ; le défaut Symfony est « H:i:s » et provoque une erreur de transformation.
                'input_format' => 'H:i',
                'constraints' => [new NotBlank(message: 'L’heure de début est obligatoire.')],
            ])
            ->add('fin_date', DateType::class, [
                'label' => false,
                'mapped' => false,
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank(message: 'La date de fin est obligatoire.')],
            ])
            ->add('fin_time', TimeType::class, [
                'label' => false,
                'mapped' => false,
                'widget' => 'single_text',
                'html5' => true,
                'input' => 'string',
                'input_format' => 'H:i',
                'constraints' => [new NotBlank(message: 'L’heure de fin est obligatoire.')],
            ])
            ->add('enLigne', CheckboxType::class, [
                'label' => 'Réunion en ligne (Visioconférence)',
                'required' => false,
            ])
            ->add('idSalle', EntityType::class, [
                'class' => Espaces::class,
                'choice_label' => 'nom',
                'label' => 'Choisir la Salle',
                'required' => false,
                'placeholder' => '-- Sélectionner une salle --',
            ])
            ->add('participants', TextType::class, [
                'label' => 'Participants',
                'required' => false,
                'attr' => [
                    'placeholder' => 'E-mails séparés par des virgules, des points-virgules ou un retour à la ligne',
                    'autocomplete' => 'off',
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }
            if (isset($data['participants']) && \is_string($data['participants'])) {
                $data['participants'] = trim($data['participants']);
                $event->setData($data);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
            $reunion = $event->getData();
            $form = $event->getForm();
            if (!$reunion instanceof Reunion) {
                return;
            }

            $dd = $reunion->getDateHeureDebut();
            if ($dd instanceof \DateTimeInterface) {
                $form->get('debut_date')->setData(\DateTimeImmutable::createFromInterface($dd));
                $form->get('debut_time')->setData($dd->format('H:i'));
            }

            $df = $reunion->getDateHeureFin();
            if ($df instanceof \DateTimeInterface) {
                $form->get('fin_date')->setData(\DateTimeImmutable::createFromInterface($df));
                $form->get('fin_time')->setData($df->format('H:i'));
            }
        });

    }

    /**
     * À appeler après handleRequest() et avant isValid(), pour remplir dateHeureDebut / Fin et contraintes métier.
     */
    public static function applyDateTimesAndBusinessRules(FormInterface $form, Reunion $reunion): void
    {
        if (!$form->has('debut_date')) {
            return;
        }

        $dde = $form->get('debut_date')->getData();
        $dte = self::normalizeTimePart($form->get('debut_time')->getData());
        $fde = $form->get('fin_date')->getData();
        $fte = self::normalizeTimePart($form->get('fin_time')->getData());

        if ($dde instanceof \DateTimeInterface && $dte !== null) {
            $start = self::combineDateTime($dde, $dte);
            if ($start !== null) {
                $reunion->setDateHeureDebut($start);
            }
        }

        if ($fde instanceof \DateTimeInterface && $fte !== null) {
            $end = self::combineDateTime($fde, $fte);
            if ($end !== null) {
                $reunion->setDateHeureFin($end);
            }
        }

        if ($reunion->getDateHeureFin() <= $reunion->getDateHeureDebut()) {
            $form->get('fin_time')->addError(new FormError('La fin doit être après le début.'));
        }

        if (!$reunion->getEnLigne() && null === $reunion->getIdSalle()) {
            $form->get('idSalle')->addError(new FormError('Choisissez une salle (non requis si « En ligne » est coché).'));
        }
    }

    private static function normalizeTimePart(mixed $v): ?string
    {
        if ($v instanceof \DateTimeInterface) {
            return $v->format('H:i');
        }
        if (\is_string($v) && trim($v) !== '') {
            return trim($v);
        }

        return null;
    }

    private static function combineDateTime(\DateTimeInterface $date, string $time): ?\DateTimeImmutable
    {
        $time = trim($time);
        if ($time === '') {
            return null;
        }
        if (preg_match('/^(\d{1,2}):(\d{2})/', $time, $m)) {
            $time = str_pad($m[1], 2, '0', STR_PAD_LEFT).':'.$m[2];
        }
        $day = \DateTimeImmutable::createFromInterface($date)->format('Y-m-d');
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $day.' '.$time);

        return $dt ?: null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reunion::class,
        ]);
    }
}
