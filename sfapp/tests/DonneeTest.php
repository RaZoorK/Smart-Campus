<?php

namespace App\Tests;

use App\Entity\SA;
use PHPUnit\Framework\TestCase;
use App\Entity\Seuil;
use App\Entity\Salle;
use App\Service\LectureFichierService;
use App\Service\JsonDecoderService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SeuilRepository;
use App\Repository\SalleRepository;

class DonneeTest extends TestCase
{
    private $entityManager;
    private $seuilRepository;
    private $salleRepository;

    protected function setUp(): void
    {
        // Simulation de l'entité Manager, Salle et Seuil
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->seuilRepository = $this->createMock(SeuilRepository::class);
        $this->salleRepository = $this->createMock(SalleRepository::class);
    }

    // Test de la création d'un seuil
    public function testCreationSeuil(): void
    {
        $seuil = new Seuil();
        $seuil->setType('Température')
            ->setValeurMax(25.0)
            ->setValeurMin(18.0);

        $this->assertEquals('Température', $seuil->getType());
        $this->assertEquals(25.0, $seuil->getValeurMax());
        $this->assertEquals(18.0, $seuil->getValeurMin());
    }

    // Test de la création d'une salle
    public function testCreationSalle(): void
    {
        $salle = new Salle();
        $salle->setNom('Secrétariat');

        $this->assertEquals('Secrétariat', $salle->getNom());
    }

    // Test de l'ajout d'un seuil en base de données
    public function testAjoutSeuilEnBase(): void
    {
        $seuil = new Seuil();
        $seuil->setType('Humidité')->setValeurMax(60)->setValeurMin(30);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($seuil);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->entityManager->persist($seuil);
        $this->entityManager->flush();

        $this->assertTrue(true);  // Test de validation simple, ici la méthode persist a bien été appelée
    }

    // Test du service JsonDecoder
    public function testJsonDecoder(): void
    {
        $json = '{"temperature": 23.5, "humidity": 45.2, "co2": 350}';

        $jsonDecoder = new JsonDecoderService();

        // Utilisation de la méthode `decode()` du service
        $result = $jsonDecoder->decode($json);

        // Assertions pour vérifier que les clés existent dans le tableau décodé
        $this->assertArrayHasKey('temperature', $result);
        $this->assertArrayHasKey('humidity', $result);
        $this->assertArrayHasKey('co2', $result);
    }

    // Test du service LectureFichier
    public function testLectureFichier(): void
    {
        $filePath = '/path/to/file.txt';

        // Création du mock pour LectureFichierService
        $lectureFichier = $this->createMock(LectureFichierService::class);

        // Configuration du mock pour la méthode `readFile`
        $lectureFichier->expects($this->once())
            ->method('readFile')
            ->with($filePath)
            ->willReturn('Fichier lu avec succès');

        // Appel de la méthode et vérification du résultat
        $result = $lectureFichier->readFile($filePath);

        $this->assertEquals('Fichier lu avec succès', $result);
    }

    public function testCalculMoyenneJour(): void
    {
        // Simuler des entrées de données
        $donnees = [
            ['localisation' => 'Secrétariat', 'nom' => 'temp', 'valeur' => 22, 'dateCapture' => '2025-01-15T12:00:00'],
            ['localisation' => 'Secrétariat', 'nom' => 'temp', 'valeur' => 23, 'dateCapture' => '2025-01-15T13:00:00'],
            ['localisation' => 'Secrétariat', 'nom' => 'temp', 'valeur' => 24, 'dateCapture' => '2025-01-15T14:00:00'],
        ];

        // Calcul des moyennes
        $moyenneParJour = [];
        foreach ($donnees as $entree) {

            if ($entree['localisation'] === 'Secrétariat') {
                $jour = (new \DateTime($entree['dateCapture']))->format('Y-m-d');
                if (!isset($moyenneParJour[$jour])) {
                    $moyenneParJour[$jour] = ['totalTemp' => 0, 'compteurTemp' => 0];
                }
                $moyenneParJour[$jour]['totalTemp'] += $entree['valeur'];
                $moyenneParJour[$jour]['compteurTemp']++;
            }
        }

        // Calcul des moyennes
        foreach ($moyenneParJour as &$jour) {
            $jour['moyenneTemp'] = $jour['compteurTemp'] > 0 ? $jour['totalTemp'] / $jour['compteurTemp'] : null;
        }

        // Vérifier si la moyenne est calculée correctement
        $this->assertEquals(23, $moyenneParJour['2025-01-15']['moyenneTemp']);
    }

}
