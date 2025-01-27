<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Salle;
use App\Entity\SA;
use App\Repository\SARepository;
use App\Model\Etat;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;


class GestionSaTest extends TestCase
{
    public function testGetSA(): void
    {
        // Créer un objet Salle
        $salle = new Salle();

        // Créer un mock de l'entité SA
        $SA = $this->createMock(SA::class);

        // Associer l'objet SA à l'objet Salle
        $salle->setSA($SA);

        // Vérifier que la méthode 'getSA' retourne bien l'objet SA associé
        $this->assertSame($SA, $salle->getSA());
    }

    public function testSetSA(): void
    {
        // Créer un objet Salle
        $salle = new Salle();

        // Créer un mock de l'entité SA
        $SA = $this->createMock(SA::class);

        // Associer l'objet SA à l'objet Salle
        $salle->setSA($SA);

        // Vérifier que la méthode 'getSA' retourne bien l'objet SA associé
        $this->assertSame($SA, $salle->getSA());
    }

    public function testGetNomSA(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir le nom de SA
        $sa->setNomSA("Nom SA Test");

        // Vérifier que la méthode 'getNomSA' retourne bien le nom défini
        $this->assertSame("Nom SA Test", $sa->getNomSA());
    }

    public function testSetNomSA(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir le nom de SA
        $sa->setNomSA("Nom SA Test");

        // Vérifier que la méthode 'getNomSA' retourne bien le nom défini
        $this->assertSame("Nom SA Test", $sa->getNomSA());
    }

    public function testGetEtat(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir l'état de l'objet SA en utilisant l'énumération
        $etat = Etat::FONCTIONNEL; // Remplacez par une valeur valide de votre enum Etat
        $sa->setEtat($etat);

        // Vérifier que la méthode 'getEtat' retourne bien l'énumération
        $this->assertSame($etat, $sa->getEtat());
    }

    public function testSetEtat(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir l'état de l'objet SA en utilisant l'énumération
        $etat = Etat::FONCTIONNEL; // Remplacez par une valeur valide de votre enum Etat
        $sa->setEtat($etat);

        // Vérifier que la méthode 'getEtat' retourne bien l'énumération
        $this->assertSame($etat, $sa->getEtat());
    }


    public function testGetSalle(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Créer un mock de l'entité Salle
        $salle = $this->createMock(Salle::class);

        // Associer la salle à l'objet SA
        $sa->setSalle($salle);

        // Vérifier que la méthode 'getSalle' retourne bien l'objet Salle
        $this->assertSame($salle, $sa->getSalle());
    }

    public function testSetSalle(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Créer un mock de l'entité Salle
        $salle = $this->createMock(Salle::class);

        // Associer la salle à l'objet SA
        $sa->setSalle($salle);

        // Vérifier que la méthode 'getSalle' retourne bien l'objet Salle
        $this->assertSame($salle, $sa->getSalle());
    }

    public function testGetEtatValue(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir l'état de l'objet SA en utilisant l'énumération
        $etat = Etat::MAINTENANCE; // Remplacez par une valeur valide de votre enum Etat
        $sa->setEtat($etat);

        // Vérifier que la méthode 'getEtatValue' retourne bien la valeur de l'énumération
        $this->assertSame($etat->value, $sa->getEtatValue());
    }

    public function testSetEtatValue(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Définir l'état de l'objet SA en utilisant l'énumération
        $etat = Etat::MAINTENANCE; // Remplacez par une valeur valide de votre enum Etat
        $sa->setEtat($etat);

        // Vérifier que la méthode 'getEtatValue' retourne bien la valeur de l'énumération
        $this->assertSame($etat->value, $sa->getEtatValue());
    }

    public function testGetNotifications(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Créer un mock de l'entité Notification
        $notification = $this->createMock(Notification::class);

        // Ajouter la notification à l'objet SA
        $sa->addNotification($notification);

        // Vérifier que la méthode 'getNotifications' retourne bien la collection contenant la notification
        $this->assertContains($notification, $sa->getNotifications());
    }

    public function testSetNotifications(): void
    {
        // Créer un objet SA
        $sa = new SA();

        // Créer un mock de l'entité Notification
        $notification = $this->createMock(Notification::class);

        // Ajouter la notification à l'objet SA
        $sa->addNotification($notification);

        // Vérifier que la méthode 'getNotifications' retourne bien la collection contenant la notification
        $this->assertContains($notification, $sa->getNotifications());
    }
}
