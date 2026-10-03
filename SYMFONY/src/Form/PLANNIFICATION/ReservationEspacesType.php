<?php

namespace App\Form\PLANNIFICATION;

use App\Entity\Espaces;
use App\Entity\Reservation_espaces;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationEspacesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idEspace', EntityType::class, [
                'class' => Espaces::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un espace',
            ])
            ->add('dateReservation', DateType::class, ['widget' => 'single_text'])
            ->add('dateHeureDebut', DateTimeType::class, ['widget' => 'single_text'])
            ->add('dateHeureFin', DateTimeType::class, ['widget' => 'single_text'])
            ->add('objectif', TextType::class)
            ->add('statut', CheckboxType::class, ['required' => false])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation_espaces::class,
        ]);
    }
}
