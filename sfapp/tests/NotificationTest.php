<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Entity\Notification;
use App\Entity\SA;
use App\Entity\Utilisateur;
use App\Model\TypeNotif;
use Doctrine\Common\Collections\ArrayCollection;

class NotificationTest extends TestCase
{

    public function testGetExpediteur(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de Utilisateur
        $expediteur = $this->createMock(Utilisateur::class);

        // Définir l'expéditeur
        $notification->setExpediteur($expediteur);

        // Vérifier que la méthode 'getExpediteur' retourne bien l'objet Utilisateur
        $this->assertSame($expediteur, $notification->getExpediteur());
    }

    public function testSetExpediteur(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de Utilisateur
        $expediteur = $this->createMock(Utilisateur::class);

        // Définir l'expéditeur
        $notification->setExpediteur($expediteur);

        // Vérifier que la méthode 'getExpediteur' retourne bien l'objet Utilisateur
        $this->assertSame($expediteur, $notification->getExpediteur());
    }

    public function testGetDestinataire(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de Utilisateur
        $destinataire = $this->createMock(Utilisateur::class);

        // Définir le destinataire
        $notification->setDestinataire($destinataire);

        // Vérifier que la méthode 'getDestinataire' retourne bien l'objet Utilisateur
        $this->assertSame($destinataire, $notification->getDestinataire());
    }

    public function testSetDestinataire(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de Utilisateur
        $destinataire = $this->createMock(Utilisateur::class);

        // Définir le destinataire
        $notification->setDestinataire($destinataire);

        // Vérifier que la méthode 'getDestinataire' retourne bien l'objet Utilisateur
        $this->assertSame($destinataire, $notification->getDestinataire());
    }

    public function testGetExpediteurNom(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le nom de l'expéditeur
        $notification->setExpediteurNom("Nom Test");

        // Vérifier que la méthode 'getExpediteurNom' retourne bien le nom défini
        $this->assertSame("Nom Test", $notification->getExpediteurNom());
    }

    public function testSetExpediteurNom(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le nom de l'expéditeur
        $notification->setExpediteurNom("Nom Test");

        // Vérifier que la méthode 'getExpediteurNom' retourne bien le nom défini
        $this->assertSame("Nom Test", $notification->getExpediteurNom());
    }

    public function testGetExpediteurPrenom(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le prénom de l'expéditeur
        $notification->setExpediteurPrenom("Prénom Test");

        // Vérifier que la méthode 'getExpediteurPrenom' retourne bien le prénom défini
        $this->assertSame("Prénom Test", $notification->getExpediteurPrenom());
    }

    public function testSetExpediteurPrenom(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le prénom de l'expéditeur
        $notification->setExpediteurPrenom("Prénom Test");

        // Vérifier que la méthode 'getExpediteurPrenom' retourne bien le prénom défini
        $this->assertSame("Prénom Test", $notification->getExpediteurPrenom());
    }

    public function testGetDateCreation(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer une DateTimeImmutable
        $date = new \DateTimeImmutable();

        // Définir la date de création
        $notification->setDateCreation($date);

        // Vérifier que la méthode 'getDateCreation' retourne bien la date définie
        $this->assertSame($date, $notification->getDateCreation());
    }

    public function testSetDateCreation(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer une DateTimeImmutable
        $date = new \DateTimeImmutable();

        // Définir la date de création
        $notification->setDateCreation($date);

        // Vérifier que la méthode 'getDateCreation' retourne bien la date définie
        $this->assertSame($date, $notification->getDateCreation());
    }

    public function testGetSujet(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le sujet
        $notification->setSujet("Sujet Test");

        // Vérifier que la méthode 'getSujet' retourne bien le sujet défini
        $this->assertSame("Sujet Test", $notification->getSujet());
    }

    public function testSetSujet(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le sujet
        $notification->setSujet("Sujet Test");

        // Vérifier que la méthode 'getSujet' retourne bien le sujet défini
        $this->assertSame("Sujet Test", $notification->getSujet());
    }

    public function testGetMessage(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le message
        $notification->setMessage("Message Test");

        // Vérifier que la méthode 'getMessage' retourne bien le message défini
        $this->assertSame("Message Test", $notification->getMessage());
    }

    public function testSetMessage(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir le message
        $notification->setMessage("Message Test");

        // Vérifier que la méthode 'getMessage' retourne bien le message défini
        $this->assertSame("Message Test", $notification->getMessage());
    }

    public function testGetSaConcerne(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de SA
        $sa = $this->createMock(SA::class);

        // Associer SA à la notification
        $notification->setSaConcerne($sa);

        // Vérifier que la méthode 'getSaConcerne' retourne bien l'objet SA
        $this->assertSame($sa, $notification->getSaConcerne());
    }

    public function testSetSaConcerne(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un mock de SA
        $sa = $this->createMock(SA::class);

        // Associer SA à la notification
        $notification->setSaConcerne($sa);

        // Vérifier que la méthode 'getSaConcerne' retourne bien l'objet SA
        $this->assertSame($sa, $notification->getSaConcerne());
    }

    public function testIsVue(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir l'état de la notification (vue ou non vue)
        $notification->setVue(true);

        // Vérifier que la méthode 'isVue' retourne bien l'état
        $this->assertTrue($notification->isVue());
    }

    public function testSetVue(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Définir l'état de la notification
        $notification->setVue(false);

        // Vérifier que la méthode 'isVue' retourne bien l'état
        $this->assertFalse($notification->isVue());
    }

    public function testGetTypeNotif(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un TypeNotif
        $type = TypeNotif::ANOMALIE; // Remplacez par une valeur valide de l'énumération

        // Définir le type de notification
        $notification->setTypeNotif($type);

        // Vérifier que la méthode 'getTypeNotif' retourne bien l'objet TypeNotif
        $this->assertSame($type, $notification->getTypeNotif());
    }

    public function testSetTypeNotif(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un TypeNotif
        $type = TypeNotif::ANOMALIE; // Remplacez par une valeur valide de l'énumération

        // Définir le type de notification
        $notification->setTypeNotif($type);

        // Vérifier que la méthode 'getTypeNotif' retourne bien l'objet TypeNotif
        $this->assertSame($type, $notification->getTypeNotif());
    }


    public function testGetTypeNotifValue(): void
    {
        // Créer un objet Notification
        $notification = new Notification();

        // Créer un TypeNotif
        $type = TypeNotif::ANOMALIE; // Remplacez par une valeur valide de l'énumération

        // Définir le type de notification
        $notification->setTypeNotif($type);

        // Vérifier que la méthode 'getTypeNotifValue' retourne bien la valeur de l'énumération
        $this->assertSame($type->value, $notification->getTypeNotifValue());
    }

}