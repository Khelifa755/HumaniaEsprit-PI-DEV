<?php

namespace App\Form\RECRUTEMENT;

use App\Entity\Candidature_externe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class Candidature_externeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('posteExterneId', IntegerType::class, [
                'label' => 'ID poste externe',
            ])
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('dateDepot', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'En attente' => 'En attente',
                    'Acceptée' => 'Acceptée',
                    'Refusée' => 'Refusée',
                    'En entretien' => 'En entretien',
                ],
            ])
            ->add('etapePipeline', ChoiceType::class, [
                'choices' => [
                    'Réception' => 'Réception',
                    'Finalisé' => 'Finalisé',
                    'Entretien' => 'Entretien',
                    'Offre' => 'Offre',
                ],
            ])
            ->add('scoringIa', NumberType::class, [
                'required' => false,
                'empty_data' => '0',
            ])
            ->add('cvUrl', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Url(
                        protocols: ['https'],
                        message: 'L\'URL du CV doit commencer par https://'
                    ),
                ],
                'attr' => [
                    'placeholder' => 'https://...',
                    'pattern' => '^https://.*',
                ],
            ])
            ->add('lettreMotivationUrl', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\Url(
                        protocols: ['https'],
                        message: 'L\'URL de la lettre doit commencer par https://'
                    ),
                ],
                'attr' => [
                    'placeholder' => 'https://...',
                    'pattern' => '^https://.*',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature_externe::class,
        ]);
    }
}
