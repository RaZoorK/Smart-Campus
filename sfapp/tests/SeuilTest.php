<?php

namespace App\Tests;

use App\Entity\Seuil;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;

class SeuilTest extends TestCase
{
    public function testGetSetType()
    {
        $seuil = new Seuil();
        $type = 'Température';

        // Vérification que la valeur par défaut est null
        $this->assertNull($seuil->getType());

        // Test setter et getter
        $seuil->setType($type);
        $this->assertEquals($type, $seuil->getType());
    }

    public function testGetSetValeurMax()
    {
        $seuil = new Seuil();
        $valeurMax = 25.5;

        // Vérification que la valeur par défaut est null
        $this->assertNull($seuil->getValeurMax());

        // Test setter et getter
        $seuil->setValeurMax($valeurMax);
        $this->assertEquals($valeurMax, $seuil->getValeurMax());
    }

    public function testGetSetValeurMin()
    {
        $seuil = new Seuil();
        $valeurMin = 18.0;

        // Vérification que la valeur par défaut est null
        $this->assertNull($seuil->getValeurMin());

        // Test setter et getter
        $seuil->setValeurMin($valeurMin);
        $this->assertEquals($valeurMin, $seuil->getValeurMin());
    }

    public function testImmutabilityAfterPersist()
    {
        $seuil = new Seuil();
        $seuil->setType('Température')
            ->setValeurMax(30)
            ->setValeurMin(15);

        // Sauvegarder l'objet dans une "base de données" simulée ou réelle
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($seuil));
        $entityManager->expects($this->once())
            ->method('flush');

        // Test pour vérifier que l'objet reste le même après la persistance
        $entityManager->persist($seuil);
        $entityManager->flush();

        // L'objet persiste correctement avec ses données intactes
        $this->assertEquals('Température', $seuil->getType());
        $this->assertEquals(30, $seuil->getValeurMax());
        $this->assertEquals(15, $seuil->getValeurMin());
    }

    public function testSetValeurMaxInferieurAValeurMin()
    {
        $seuil = new Seuil();

        // Définir une valeur minimale
        $seuil->setValeurMin(10);

        // Vérifier que l'exception est levée lors de la définition de la valeur maximale inférieure à la valeur minimale
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur maximale doit être supérieure ou égale à la valeur minimale.');

        // Définir une valeur maximale inférieure à la valeur minimale
        $seuil->setValeurMax(5);
    }

    public function testSetValeurMinSuperieurAValeurMax()
    {
        $seuil = new Seuil();

        // Définir une valeur maximale
        $seuil->setValeurMax(10);

        // Vérifier que l'exception est levée lors de la définition de la valeur minimale supérieure à la valeur maximale
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('La valeur minimale doit être inférieure ou égale à la valeur maximale.');

        // Définir une valeur minimale supérieure à la valeur maximale
        $seuil->setValeurMin(15);
    }



}
