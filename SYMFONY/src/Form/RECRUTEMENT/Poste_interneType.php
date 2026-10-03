<?php

namespace App\Form\RECRUTEMENT;

use App\Entity\Poste_interne;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Poste_interneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('typePoste', ChoiceType::class, [
                'choices' => [
                    'MISSION INTERNE' => 'Mission interne',
                    'MISSION EXTERNE' => 'Mission externe',
                    'RENFORT' => 'Renfort',
                ],
            ])
            ->add('remuneration', NumberType::class, [
                'constraints' => [
                    new Assert\NotBlank(message: 'La rémunération est obligatoire.'),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poste_interne::class,
            'constraints' => [
                new Assert\Callback(function (Poste_interne $posteInterne, ExecutionContextInterface $context): void {
                    if ($posteInterne->getDateDebut() > $posteInterne->getDateFin()) {
                        $context
                            ->buildViolation('La date de début ne peut pas être après la date de fin.')
                            ->atPath('dateFin')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
