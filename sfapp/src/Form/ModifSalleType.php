<?php
// src/Form/ModifSalleType.php

namespace App\Form;

use App\Entity\SA;
use App\Entity\Salle;
use SebastianBergmann\CodeCoverage\Report\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType; // Ajouter TextType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ModifSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $salleActuelle = $options['salle_actuelle'];
        $choixBatiments = $options['choix_batiments'] ?? []; // Récupérer les choix ou utiliser un tableau vide

        $builder
            ->add('Nom', TextType::class, [
                'label' => 'Nom de la salle',
                'required' => true,
            ])
            ->add('Sa', EntityType::class, [
                'class' => SA::class,
                'query_builder' => function (EntityRepository $er) use ($salleActuelle) {
                    return $er->createQueryBuilder('sa')
                        ->leftJoin('sa.salle', 'salle')
                        ->where('salle IS NULL OR salle = :currentSalle')
                        ->setParameter('currentSalle', $salleActuelle);
                },
                'choice_label' => 'nomSA',
                'placeholder' => 'Sélectionnez un SA non affecté',
                'required' => false,
                'label' => 'Nom du SA',
            ])
            ->add('Batiment', ChoiceType::class, [
                'label' => 'Nom du bâtiment',
                'choices' => $choixBatiments, // Liste des bâtiments existants
                'placeholder' => 'Sélectionnez un bâtiment',
                'required' => true,
            ])
            ->add('Etage', TextType::class, [
                'label' => 'Numéro d\'étage',
                'required' => true,
                'data' => $salleActuelle->getEtage(),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
        ]);
        $resolver->setDefined('salle_actuelle'); // Définir l'option 'salle_actuelle'
        $resolver->setDefined('choix_batiments'); // Définir l'option 'choix_batiments'
    }

}