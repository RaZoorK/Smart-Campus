<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Model\TypeNotif;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class FiltreNotifType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le sujet
            ->add('sujet', TextType::class, [
                'required' => false,
            ])

            ->add('vue', CheckboxType::class, [
                'required' => false, // Cette option permet de ne pas obliger l'utilisateur à cocher la case
                'label' => 'Que les non vues',
            ])

            // Filtre par expéditeur
            ->add('expediteur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'prenom', // Assume que l'entité Utilisateur a un champ "nom"
                'required' => false,
                'placeholder' => 'Envoyé par tous',
            ])

            // Filtre par expéditeur
            ->add('destinataire', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => 'prenom', // Assume que l'entité Utilisateur a un champ "nom"
                'required' => false,
                'placeholder' => 'Envoyé pour tous',
            ])

            // Filtre par date : options "moins de 2 jours", "moins de 5 jours", "plus de 5 jours"
            ->add('dateFilter', ChoiceType::class, [
                'choices' => [
                    'Moins de 2 jours' => 'moins_2_jours',
                    'Moins de 5 jours' => 'moins_5_jours',
                    'Plus de 5 jours' => 'plus_5_jours',
                ],
                'required' => false,
                'label' => 'Date',
                'placeholder' => 'Date Aucune',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // Pas de mapping direct avec une entité
        ]);
    }
}
