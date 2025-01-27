<?php

namespace App\Form;

use App\Entity\Salle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SalleChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choix_batiments = $options['choix_batiments'];
        $choix_etages = $options['choix_etages'];

        $builder
            ->add('nom', null, [
                'label' => 'Nom de la salle',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Rechercher une salle par nom',
                ],
            ])
            ->add('etage', ChoiceType::class, [
                'label' => 'Sélectionner un étage',
                'required' => false,
                'choices' => $choix_etages, // Liste dynamique des étages
                'placeholder' => 'Tous les étages',
                'empty_data' => null,
            ])
            ->add('batiment', ChoiceType::class, [
                'label' => 'Bâtiment',
                'required' => false,
                'choices' => $choix_batiments, // Liste dynamique des bâtiments
                'placeholder' => 'Tous les bâtiments',
                'empty_data' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
            'choix_batiments' => [], // Paramètre pour passer les choix de bâtiments
            'choix_etages' => [],    // Paramètre pour passer les choix d'étages
        ]);
    }
}
