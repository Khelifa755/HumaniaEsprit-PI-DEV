<?php

namespace App\Form\PLANNIFICATION;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => [
                    'maxlength' => 255,
                    'minlength' => 1,
                    'required' => true,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'rows' => 4,
                    'maxlength' => 10000,
                    'minlength' => 1,
                    'required' => true,
                ],
            ])
            ->add('dateEvenement', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['required' => true],
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Début',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['required' => true],
            ])
            ->add('dateHeureFin', DateTimeType::class, [
                'label' => 'Fin',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['required' => true],
            ])
            ->add('lieu', TextType::class, [
                'label' => 'Lieu',
                'attr' => [
                    'maxlength' => 255,
                    'minlength' => 1,
                    'required' => true,
                ],
            ])
            ->add('nbParticipantsMax', IntegerType::class, [
                'label' => 'Participants max',
                'attr' => [
                    'min' => 1,
                    'max' => 1000000,
                    'required' => true,
                ],
            ])
            ->add('participantsInscrits', TextareaType::class, [
                'label' => 'Participants inscrits (optionnel)',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'rows' => 2,
                    'maxlength' => 5000,
                ],
            ])
            ->add('creePar', TextType::class, [
                'label' => 'Créé par',
                'attr' => [
                    'maxlength' => 255,
                    'minlength' => 1,
                    'required' => true,
                ],
            ])
            ->add('creeLe', DateTimeType::class, [
                'label' => 'Créé le',
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'attr' => ['required' => true],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
    }
}
