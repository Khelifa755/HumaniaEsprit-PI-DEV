<?php

namespace App\Form\PLANNIFICATION;

use App\Entity\Espaces;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EspacesType extends AbstractType
{
    /** @var array<string, string> */
    private const TYPE_CHOICES = [
        'Salle de réunion' => 'Salle de réunion',
        'Coworking' => 'Coworking',
    ];

    /** @var array<string, string> */
    private const EQUIPEMENT_CHOICES = [
        'Projecteur' => 'Projecteur',
        'Tableau blanc' => 'Tableau blanc',
        'Ordinateur' => 'Ordinateur',
        'Climatisation' => 'Climatisation',
        'Wifi' => 'Wifi',
        'Imprimante' => 'Imprimante',
        'Telephone' => 'Telephone',
        'Visioconference' => 'Visioconference',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'maxlength' => 255,
                    'minlength' => 1,
                    'required' => true,
                    'autocomplete' => 'off',
                ],
            ])
            ->add('capacite', IntegerType::class, [
                'label' => 'Capacité',
                'attr' => [
                    'min' => 1,
                    'max' => 100000,
                    'required' => true,
                ],
            ])
            ->add('etage', IntegerType::class, [
                'label' => 'Étage',
                'attr' => [
                    'min' => 0,
                    'max' => 200,
                    'required' => true,
                ],
            ])
            ->add('typeEspace', ChoiceType::class, [
                'label' => 'Type d’espace',
                'choices' => self::TYPE_CHOICES,
                'expanded' => true,
                'multiple' => false,
                'required' => true,
                'row_attr' => ['class' => 'form-group form-choice-type-espace'],
                'attr' => ['data-type-espace-radios' => ''],
            ])
            ->add('listeEquipements', ChoiceType::class, [
                'label' => 'Équipements',
                'choices' => self::EQUIPEMENT_CHOICES,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'row_attr' => ['class' => 'form-group form-choice-equipements'],
                'attr' => ['data-equipements-grid' => ''],
            ])
            ->add('urlImage', TextareaType::class, [
                'label' => 'URL de l’image',
                'attr' => [
                    'rows' => 2,
                    'maxlength' => 2000,
                    'placeholder' => 'https://…',
                ],
            ])
            ->add('disponible', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
            ])
        ;

        $builder->get('typeEspace')->addModelTransformer(new CallbackTransformer(
            function (?string $stored): string {
                if ($stored === 'Réunion' || $stored === 'Reunion') {
                    return 'Salle de réunion';
                }
                if ($stored === 'Salle de réunion' || $stored === 'Coworking') {
                    return $stored;
                }

                return 'Salle de réunion';
            },
            function (?string $submitted): string {
                return $submitted ?? 'Salle de réunion';
            }
        ));

        $allowedEquip = array_flip(array_keys(self::EQUIPEMENT_CHOICES));
        $builder->get('listeEquipements')->addModelTransformer(new CallbackTransformer(
            function (?string $stored) use ($allowedEquip): array {
                if ($stored === null || $stored === '') {
                    return [];
                }
                $out = [];
                foreach (array_map('trim', explode(',', $stored)) as $p) {
                    if ($p !== '' && isset($allowedEquip[$p])) {
                        $out[] = $p;
                    }
                }

                return $out;
            },
            function (?array $selected): string {
                if ($selected === null || $selected === []) {
                    return '';
                }
                $ordered = [];
                foreach (array_keys(self::EQUIPEMENT_CHOICES) as $label) {
                    if (\in_array($label, $selected, true)) {
                        $ordered[] = $label;
                    }
                }

                return implode(', ', $ordered);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Espaces::class,
        ]);
    }
}
