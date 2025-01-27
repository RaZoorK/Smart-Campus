<?php

namespace App\Form;

use App\Entity\Salle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalleFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'label' => 'Nom de la Salle',
                'attr' => ['placeholder' => 'Rechercher par nom']
            ])
            ->add('batiment', ChoiceType::class, [
                'required' => false,
                'label' => 'Bâtiment',
                'placeholder' => 'Tous les bâtiments',
                'choices' => $options['choix_batiments'], // Les bâtiments dynamiques
            ])
            ->add('etage', ChoiceType::class, [
                'required' => false,
                'label' => 'Étage',
                'placeholder' => 'Tous les étages',
                'choices' => $options['choix_etages'], // Les étages dynamiques
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);

        $resolver->setRequired(['choix_batiments', 'choix_etages']);
    }
}