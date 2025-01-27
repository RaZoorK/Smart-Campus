<?php

namespace App\Form;

use App\Entity\SA;
use App\Entity\Salle;
use App\Model\Etat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
// Autres use nécessaires

class AjoutSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomSA', TextType::class, [
                'required' => true,
                'label' => 'Nom du SA',
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    Etat::FONCTIONNEL->value => Etat::FONCTIONNEL,
                    Etat::MAINTENANCE->value => Etat::MAINTENANCE,
                ],
                'required' => true,
                'label' => 'Etat',
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class,  // Utilise 'class' ici pour lier avec l'entité Salle
                'choice_label' => 'nom',   // Spécifie la propriété à afficher (par exemple l'id ou autre)
                'required' => false,
                'label' => 'Salle affectation',
                'attr' => [
                    'id' => 'sa_salle'
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SA::class,
        ]);
    }
}

