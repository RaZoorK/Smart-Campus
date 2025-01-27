<?php

namespace App\DataFixtures;

use App\Entity\SA;
use App\Entity\Salle;
use App\Entity\Seuil;
use App\Model\Etat;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création d'utilisateurs
        $tech = new Utilisateur();
        $tech->setIdentifiant("tech")
            ->setNom("Dupont")
            ->setPrenom("Philippe")
            ->setMail("philippe@dupont.com")
            ->setTelephone("0123456789")
            ->setFonction("Technicien")
            ->setRoles(["ROLE_TECHNICIEN"])
            ->setPassword($this->passwordHasher->hashPassword($tech, "tech"));
        $manager->persist($tech);

        $admin = new Utilisateur();
        $admin->setIdentifiant("admin")
            ->setNom("Ghamri")
            ->setPrenom("Yacine")
            ->setMail("yacine@ghamri.com")
            ->setTelephone("0123456780")
            ->setFonction("Manager")
            ->setRoles(["ROLE_ADMIN"])
            ->setPassword($this->passwordHasher->hashPassword($admin, "admin"));
        $manager->persist($admin);

        // Salles et associations depuis le tableau
        $data = [
            ['nomSA' => 'ESP-004', 'localisation' => 'D205'],
            ['nomSA' => 'ESP-008', 'localisation' => 'D206'],
            ['nomSA' => 'ESP-006', 'localisation' => 'D207'],
            ['nomSA' => 'ESP-014', 'localisation' => 'D204'],
            ['nomSA' => 'ESP-012', 'localisation' => 'D203'],
            ['nomSA' => 'ESP-005', 'localisation' => 'D303'],
            ['nomSA' => 'ESP-011', 'localisation' => 'D304'],
            ['nomSA' => 'ESP-007', 'localisation' => 'C101'],
            ['nomSA' => 'ESP-024', 'localisation' => 'D109'],
            ['nomSA' => 'ESP-026', 'localisation' => 'Secrétariat'],
            ['nomSA' => 'ESP-030', 'localisation' => 'D001'],
            ['nomSA' => 'ESP-028', 'localisation' => 'D002'],
            ['nomSA' => 'ESP-020', 'localisation' => 'D004'],
            ['nomSA' => 'ESP-021', 'localisation' => 'C004'],
        ];

        $salles = [];
        foreach ($data as $row) {
            // Créer ou récupérer la salle
            $nomSalle = $row['localisation'];
            if (!isset($salles[$nomSalle])) {
                $salle = new Salle();
                $salle->setNom($nomSalle)
                    ->setBatiment("IUT")
                    ->setEtage(preg_match('/\d/', $nomSalle, $matches) ? (int)$matches[0] : 0);
                $salles[$nomSalle] = $salle;
                $manager->persist($salle);
            } else {
                $salle = $salles[$nomSalle];
            }

            // Créer le SA et l'associer à la salle
            $sa = new SA();
            $sa->setNomSA($row['nomSA'])
                ->setSalle($salle)
                ->setEtat(Etat::FONCTIONNEL); // Par défaut, on met l'état à Fonctionnel
            $manager->persist($sa);
        }

        // Création des seuils
        $seuils = [
            ['type' => 'Température', 'valeurMax' => 21, 'valeurMin' => 17],
            ['type' => 'Humidité', 'valeurMax' => 70, 'valeurMin' => 30],
            ['type' => 'CO2', 'valeurMax' => 1000, 'valeurMin' => 400],
        ];

        foreach ($seuils as $seuilData) {
            $seuil = new Seuil();
            $seuil->setType($seuilData['type'])
                ->setValeurMax($seuilData['valeurMax'])
                ->setValeurMin($seuilData['valeurMin']);
            $manager->persist($seuil);
        }

        $manager->flush();
    }
}
