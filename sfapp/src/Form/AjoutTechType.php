<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use App\Repository\UtilisateurRepository;



class AjoutTechType extends AbstractType
{
    private UtilisateurRepository $utilisateurRepository;

    public function __construct(UtilisateurRepository $utilisateurRepository)
    {
        $this->utilisateurRepository = $utilisateurRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('identifiant', null, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/^\S+$/', // Interdit les espaces
                        'message' => 'L\'identifiant ne doit pas contenir d\'espaces.',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        // Vérifier si l'identifiant existe déjà
                        $existeId = $this->utilisateurRepository->findOneBy(['identifiant' => $value]);

                        if ($existeId) {
                            $context->buildViolation("L'identifiant '$value' existe déjà.")
                                ->addViolation();
                        }
                    }),
                ],
            ])
            ->add('password')
            ->add('nom')
            ->add('prenom')
            ->add('mail', EmailType::class, [
                'constraints' => [
                    new Email([
                        'message' => 'Email invalide',
                    ]),
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        // Vérifier si le numéro de téléphone existe déjà
                        $existeMail = $this->utilisateurRepository->findOneBy(['mail' => $value]);

                        if ($existeMail) {
                            $context->buildViolation("Le mail '$value' existe déjà.")
                                ->addViolation();
                        }
                    })
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
                    new Callback(function ($value, ExecutionContextInterface $context) {
                        // Vérifier si le numéro de téléphone existe déjà
                        $existeTel = $this->utilisateurRepository->findOneBy(['telephone' => $value]);

                        if ($existeTel) {
                            $context->buildViolation("Le numéro de téléphone '$value' existe déjà.")
                                ->addViolation();
                        }
                    })
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
