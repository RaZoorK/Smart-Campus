<?php

namespace App\Form;

use App\Entity\Seuil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeuilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajout des champs du formulaire
        $builder
            ->add('valeurMin', NumberType::class, [
                'label' => 'Valeur minimale',
                'required' => false, // Rend ce champ optionnel
                'empty_data' => '0',  // Définit une valeur par défaut si le champ est vide
            ])
            ->add('valeurMax', NumberType::class, [
                'label' => 'Valeur maximale',
                'required' => false, // Rend ce champ optionnel
                'empty_data' => '0',  // Définit une valeur par défaut si le champ est vide
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seuil::class, // Assurez-vous que 'data_class' est correctement défini
        ]);
    }
}

