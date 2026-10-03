<?php

namespace App\Form\CONGE;

use App\Entity\Conge;
use App\Entity\Type_conge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class CongeType extends AbstractType
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $canChooseStatut = $options['can_choose_statut'];

        // Récupérer les types depuis la BDD
        $types = $this->em->getRepository(Type_conge::class)->findAll();
        $choices = [];
        foreach ($types as $type) {
            // Garder le libellé exact de la BDD
            $choices[$type->getLibelle()] = $type->getId();
        }

        $builder
            ->add('dateDebut', DateType::class, [
                'label'       => false,
                'widget'      => 'single_text',
                'html5'       => true,
                'attr'        => ['class' => 'conge-date-input', 'id' => 'conge_date_debut'],
                'constraints' => [
                    new NotBlank(['message' => 'La date de début est obligatoire.']),
                ],
            ])

            ->add('dateFin', DateType::class, [
                'label'       => false,
                'widget'      => 'single_text',
                'html5'       => true,
                'attr'        => ['class' => 'conge-date-input', 'id' => 'conge_date_fin'],
                'constraints' => [
                    new NotBlank(['message' => 'La date de fin est obligatoire.']),
                ],
            ])

            ->add('nbrJours', IntegerType::class, [
                'label'    => false,
                'mapped'   => true,
                'required' => false,
                'attr'     => [
                    'readonly' => true,
                    'id'       => 'conge_nbr_jours',
                    'class'    => 'conge-nbr-display',
                ],
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
                'constraints' => [
                    new NotBlank(['message' => 'Le statut est obligatoire.']),
                ],
            ]);
        } else {
            $builder->add('statut', HiddenType::class, [
                'required' => false,
            ]);
        }

        $builder
            ->add('typeCongeId', ChoiceType::class, [
                'label'       => false,
                'choices'     => $choices,
                'placeholder' => '-- Sélectionner un type de congé --',
                'required'    => true,
                'constraints' => [
                    new NotNull(['message' => 'Le type de congé est obligatoire.']),
                ],
            ])

            ->add('utilisateurId', IntegerType::class, [
                'label'    => false,
                'required' => false,
                'attr'     => ['class' => 'conge-user-input'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Conge::class,
            'can_choose_statut' => true,
        ]);
        $resolver->setAllowedTypes('can_choose_statut', 'bool');
    }
}