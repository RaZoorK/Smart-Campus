<?php

namespace App\Form;

use App\Entity\Notification;
use App\Entity\Utilisateur;
use App\Entity\SA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnomalieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $role = $options['role'] ?? null;
        $builder
            // le technicien et le manager n'ont pas besoin de s'identifier
            // choisir le sujet
            ->add('sujet', TextType::class, [
                'label' => 'Sujet',
                'required' => true,
            ])
            // choisir le message
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => true,
            ])
            // choisir le SA
            ->add('saConcerne', EntityType::class, [
                'class' => SA::class,
                'choice_label' => 'nomSA',
                'label' => 'SA Concerné',
                'placeholder' => 'Sélectionnez un SA',
                'required' => true,
            ]);
        if ($role === 'Occupant') {
            $builder
                // L'occupant doit donner son nom pour être identifié par le manager
                ->add('expediteurNom', TextType::class, [
                    'required' => true,
                    'label' => 'Nom de l\'expéditeur',
                    'attr' => [
                        'maxlength' => 255,
                        'placeholder' => 'Entrez le nom de l\'expéditeur'
                    ],
                ])
                // L'occupant doit donner son prénom pour être identifié par le manager
                ->add('expediteurPrenom', TextType::class, [
                    'required' => true,
                    'label' => 'Prénom de l\'expéditeur',
                    'attr' => [
                        'maxlength' => 255,
                        'placeholder' => 'Entrez le prénom de l\'expéditeur'
                    ],
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notification::class,
            'role' => null, // Ajoute une option "role" au formulaire
        ]);
    }
}
