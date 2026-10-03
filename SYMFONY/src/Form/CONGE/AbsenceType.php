<?php

namespace App\Form\CONGE; 

use App\Entity\Absence;
use App\Entity\Type_absence;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class AbsenceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $canChooseStatut = $options['can_choose_statut'];

        // ── Heures disponibles (7h → 18h) ──────────────────────────
        $heures = [];
        for ($h = 7; $h <= 18; $h++) {
            $label = str_pad($h, 2, '0', STR_PAD_LEFT);
            $heures[$label] = $label;
        }

        // ── Minutes disponibles ─────────────────────────────────────
        $minutes = ['00' => '00', '15' => '15', '30' => '30', '45' => '45'];

        $builder
            // ── Date (single_text) ──────────────────────────────────
            ->add('dateDebut', DateType::class, [
                'label'       => false,
                'widget'      => 'single_text',
                'html5'       => true,
                'constraints' => [new NotBlank(['message' => 'La date est obligatoire.'])],
            ])
            ->add('dateFin', DateType::class, [
                'label'       => false,
                'widget'      => 'single_text',
                'html5'       => true,
                'constraints' => [new NotBlank(['message' => 'La date de fin est obligatoire.'])],
            ])

            // ── Heure début : HH séparé + MM séparé (comme image 1) ─
            ->add('heureDebutH', ChoiceType::class, [
                'label'       => false,
                'mapped'      => false,
                'choices'     => $heures,
                'placeholder' => 'HH',
                'required'    => false,
                'attr'        => ['id' => 'heureDebutH', 'class' => 'time-select'],
            ])
            ->add('heureDebutM', ChoiceType::class, [
                'label'       => false,
                'mapped'      => false,
                'choices'     => $minutes,
                'placeholder' => 'MM',
                'required'    => false,
                'attr'        => ['id' => 'heureDebutM', 'class' => 'time-select'],
            ])

            // ── Heure fin : HH séparé + MM séparé ──────────────────
            ->add('heureFinH', ChoiceType::class, [
                'label'       => false,
                'mapped'      => false,
                'choices'     => $heures,
                'placeholder' => 'HH',
                'required'    => false,
                'attr'        => ['id' => 'heureFinH', 'class' => 'time-select'],
            ])
            ->add('heureFinM', ChoiceType::class, [
                'label'       => false,
                'mapped'      => false,
                'choices'     => $minutes,
                'placeholder' => 'MM',
                'required'    => false,
                'attr'        => ['id' => 'heureFinM', 'class' => 'time-select'],
            ])

            // ── Champs cachés heureDebut / heureFin (string HH:MM) ──
            // Ces champs sont mappés et envoyés au Controller via JS
            ->add('heureDebut', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['style' => 'display:none', 'id' => 'heureDebutHidden'],
            ])
            ->add('heureFin', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['style' => 'display:none', 'id' => 'heureFinHidden'],
            ])

            // ── dureeMinutes caché (calculé par JS + Controller) ────
            ->add('dureeMinutes', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['style' => 'display:none', 'id' => 'dureeMinutesHidden'],
            ])
        ;

        if ($canChooseStatut) {
            $builder->add('statut', ChoiceType::class, [
                'label'       => false,
                'choices'     => [
                    'En attente' => 'En attente',
                    'Approuvé'   => 'Approuvé',
                    'Refusé'     => 'Refusé',
                ],
                'placeholder' => '-- Sélectionner un statut --',
                'constraints' => [new NotBlank(['message' => 'Le statut est obligatoire.'])],
            ]);
        } else {
            $builder->add('statut', HiddenType::class, [
                'required' => false,
            ]);
        }

        $builder

            // ── Type d'absence : dropdown depuis la BDD ─────────────
            ->add('typeAbsenceId', EntityType::class, [
                'label'        => false,
                'class'        => Type_absence::class,
                'mapped'       => false,   // on récupère l'ID manuellement dans le Controller
                'choice_label' => static function (Type_absence $ta): string {
                    foreach (['getLibelle', 'getNom', 'getType', 'getDesignation'] as $g) {
                        if (method_exists($ta, $g)) {
                            $v = $ta->$g();
                            if ($v !== null && $v !== '') return (string) $v;
                        }
                    }
                    return 'Type #' . $ta->getId();
                },
                'choice_value'  => static fn (?Type_absence $ta): string => $ta ? (string) $ta->getId() : '',
                'placeholder'   => '-- Sélectionner --',
                'required'      => true,
                'constraints'   => [new NotNull(['message' => 'Le type est obligatoire.'])],
                'query_builder' => static function (EntityRepository $er) {
                    return $er->createQueryBuilder('ta')
                        ->orderBy('ta.id', 'ASC')
                        ->setMaxResults(99);
                },
            ])

            // ── Utilisateur caché ───────────────────────────────────
            ->add('utilisateurId', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['style' => 'display:none'],
            ])

            // ── Motif ───────────────────────────────────────────────
            ->add('motif', TextType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Ex : Rendez-vous médical…'],
            ])

            // ── nbrJours caché ──────────────────────────────────────
            ->add('nbrJours', IntegerType::class, [
                'label'    => false,
                'mapped'   => false,
                'required' => false,
                'attr'     => ['style' => 'display:none'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'         => Absence::class,
            'can_choose_statut'  => true,
        ]);
        $resolver->setAllowedTypes('can_choose_statut', 'bool');
    }
}