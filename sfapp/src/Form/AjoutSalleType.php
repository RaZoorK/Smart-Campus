<?php

namespace App\Form;

use App\Entity\Salle;
use App\Entity\SA;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AjoutSalleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer la liste des bâtiments dynamiquement depuis les options
        $choix_batiments = $options['choix_batiments'];

        $builder
            ->add('nom', null, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 4]), // Ajouter une contrainte de longueur minimale si nécessaire
                ],
                'label' => 'Nom de la salle',
            ])
            // Champ batiment avec une option "Autre"
            ->add('batiment', ChoiceType::class, [
                'label' => 'Nom du bâtiment',
                'required' => true,
                'choices' => $choix_batiments + ['Autre' => 'Autre'], // Ajouter l'option "Autre"
                'placeholder' => 'Sélectionner un bâtiment',
                'mapped' => false, // Cela empêche ce champ d'être directement mappé à l'entité
            ])
            ->add('nouveauBatiment', TextType::class, [
                'label' => 'Nom du nouveau bâtiment',
                'required' => false,
                'mapped' => false, // Champ non mappé à l'entité
                'attr' => [
                    'placeholder' => 'Entrez le nom du bâtiment si "Autre" est choisi',
                    'class' => 'nouveau-batiment', // Ajout d'une classe pour la manipulation
                ]
            ])
            ->add('SA', EntityType::class, [
                'class' => SA::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('sa')
                        ->leftJoin('sa.salle', 'salle')
                        ->where('salle IS NULL'); // SA non affectés
                },
                'choice_label' => 'nomSA', // Champ à afficher dans la liste
                'placeholder' => 'Sélectionnez un SA non affecté',
                'required' => false,
                'label' => 'Choisir un SA',
            ])
            ->add('Etage', TextType::class, [
                'label' => 'Numéro d\'étage', // Label à afficher pour ce champ
                'required' => true, // Vous pouvez changer à true si le champ est obligatoire
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Salle::class,
            'choix_batiments' => [], // Paramètre pour passer les choix de bâtiments
        ]);
    }
}