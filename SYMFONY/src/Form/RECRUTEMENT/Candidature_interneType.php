<?php

namespace App\Form\RECRUTEMENT;

use App\Entity\Candidature_interne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Candidature_interneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('posteActuel', TextType::class, [
                'label' => 'Poste actuel',
            ])
            ->add('nouveauPoste', TextType::class, [
                'label' => 'Nouveau poste',
            ])
            ->add('nouveauSalaire', NumberType::class, [
                'label' => 'Nouveau salaire',
            ])
            ->add('dateDemande', DateType::class, [
                'label' => 'Date de la demande',
                'widget' => 'single_text',
            ])
            ->add('motif', TextareaType::class, [
                'label' => 'Motif',
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'En attente' => 'En attente',
                    'Approuvée' => 'Approuvée',
                    'Refusée' => 'Refusée',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature_interne::class,
        ]);
    }
}
