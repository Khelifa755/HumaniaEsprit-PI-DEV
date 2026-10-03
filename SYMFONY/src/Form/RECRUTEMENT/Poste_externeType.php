<?php

namespace App\Form\RECRUTEMENT;

use App\Entity\Poste_externe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class Poste_externeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class)
            ->add('typeContrat', ChoiceType::class, [
                'choices' => [
                    'CDI' => 'CDI',
                    'CDD' => 'CDD',
                    'Stage' => 'STAGE',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('salaire', NumberType::class)
            ->add('competencesRequises', TextareaType::class)
            ->add('experienceRequise', TextType::class)
            ->add('niveauEtudeRequis', TextType::class)
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Ouvert' => 'Ouvert',
                    'Fermé' => 'Fermé',
                ],
            ])
            ->add('datePublication', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('dateCloture', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('nombreEmploye', IntegerType::class)
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Haute' => 'Haute',
                    'Moyenne' => 'Moyenne',
                    'Basse' => 'Basse',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Poste_externe::class,
            'constraints' => [
                new Assert\Callback(function (Poste_externe $posteExterne, ExecutionContextInterface $context): void {
                    $datePublication = $posteExterne->getDatePublication();
                    $dateCloture = $posteExterne->getDateCloture();

                    if ($datePublication > $dateCloture) {
                        $context
                            ->buildViolation('La date de publication ne peut pas être après la date de clôture.')
                            ->atPath('dateCloture')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
