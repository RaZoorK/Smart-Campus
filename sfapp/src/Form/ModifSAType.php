<?php

namespace App\Form;

use App\Entity\SA;
use App\Entity\Salle;
use App\Model\Etat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Repository\SalleRepository;

class ModifSAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Récupérer la valeur de l'option 'saActuel'
        $currentSA = $options['saActuel'];

        $builder
            ->add('nomSA', TextType::class, [
                'required' => true,
                'label' => 'Nouveau nom du SA',
            ])
            ->add('etat', ChoiceType::class, [
                'choices' => [
                    Etat::FONCTIONNEL->value => Etat::FONCTIONNEL,
                    Etat::MAINTENANCE->value => Etat::MAINTENANCE,
                ],
                'required' => true,
                'label' => 'Nouvel état',
            ])
            ->add('salle', EntityType::class, [
                'class' => Salle::class, // L'entité associée
                'choice_label' => 'nom', // La propriété à afficher
                'required' => false,
                'label' => 'Nouvelle salle',
                'query_builder' => function (SalleRepository $repo) use ($currentSA) {
                    // Utilisation de 'use' pour passer $currentSA dans le callback
                    return $repo->createQueryBuilder('s')
                        ->where('s.SA IS NULL OR s.SA = :currentSA') // Vérifie si la salle est libre ou associée au SA actuel
                        ->setParameter('currentSA', $currentSA);
                },
                'attr' => [
                    'id' => 'sa_salle',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SA::class,
        ]);
        $resolver->setDefined('saActuel'); // Définir l'option 'saActuel'
    }
}
