<?php
namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Email;

class ModifProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifiant', TextType::class, [
                'disabled' => true, // Empêche la modification
                'label' => 'Identifiant', // Optionnel, mais améliore l'affichage
                'attr' => ['readonly' => true], // Indique visuellement que le champ est non modifiable
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false, // Le mot de passe est facultatif
                'mapped' => false,   // Ne pas mapper directement ce champ à l'entité
                'label' => 'Mot de passe',
                'attr' => ['placeholder' => 'Laisser vide pour ne pas modifier']
            ])
            ->add('nom', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z\-]+$/', // Le nom ne doit contenir que des lettres et des tirets
                        'message' => 'Le nom ne doit contenir que des lettres et des tirets.',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z\-]+$/', // Le prénom ne doit contenir que des lettres et des tirets
                        'message' => 'Le prénom ne doit contenir que des lettres et des tirets.',
                    ]),
                ],
            ])
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Email invalide.',
                    ]),
                ],
            ])
            ->add('telephone', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d{10}$/', // Le numéro doit contenir exactement 10 chiffres
                        'message' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
                    ]),
                    new Regex([
                        'pattern' => '/^\S+$/', // Interdit les espaces
                        'message' => 'Le numéro de téléphone ne doit pas contenir d\'espaces.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
