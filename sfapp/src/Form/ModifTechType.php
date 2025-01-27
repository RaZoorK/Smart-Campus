<?php

namespace App\Form;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ModifTechType extends AbstractType
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifiant', TextType::class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\S+$/', // Interdit les espaces
                        'message' => 'L\'identifiant ne doit pas contenir d\'espaces.',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) use ($options) {
                        // Si l'identifiant n'a pas été modifié, ne pas vérifier
                        $utilisateur = $options['data'];

                        // Si l'identifiant a changé, vérifier l'unicité
                        if ($utilisateur->getIdentifiant() !== $value) {
                            $existeId = $this->utilisateurRepository->findOneBy(['identifiant' => $value]);

                            if ($existeId) {
                                $context->buildViolation("L'identifiant '$value' existe déjà.")
                                    ->addViolation();
                            }
                        }
                    }),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                'required' => false, // Make the password field optional
                'mapped' => false,   // Do not map this field directly to the entity
                'label' => 'Mot de passe',
            ])
            ->add('nom')
            ->add('prenom')
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Email invalide',
                    ]),
                ],
            ])
            ->add('telephone', null, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\d{10}$/', // Exemple : 10 chiffres pour un numéro français
                        'message' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
                    ]),
                    new Regex([
                        'pattern' => '/^\S+$/', // Interdit les espaces
                        'message' => 'Le numéro ne doit pas contenir d\'espaces.',
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
