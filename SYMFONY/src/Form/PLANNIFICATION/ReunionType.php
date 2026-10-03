<?php

namespace App\Form\PLANNIFICATION;

use App\Entity\Espaces;
use App\Entity\Reunion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReunionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('dateHeureDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('dateHeureFin', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('idSalle', EntityType::class, [
                'class' => Espaces::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un espace',
            ])
            ->add('nomOrganisateur', TextType::class)
            ->add('emailOrganisateur', EmailType::class)
            ->add('participants', TextareaType::class)
            ->add('statut', CheckboxType::class, ['required' => false])
            ->add('enLigne', CheckboxType::class, ['required' => false])
            ->add('creeLe', DateTimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('zoom_meeting_id', TextType::class)
            ->add('zoom_join_url', TextType::class)
            ->add('zoom_start_url', TextType::class)
            ->add('zoom_password', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reunion::class,
        ]);
    }
}
