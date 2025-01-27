<?php
// tests/Entity/UtilisateurTest.php

namespace App\Tests\Entity;

use App\Entity\Utilisateur;
use PHPUnit\Framework\TestCase;

class UtilisateurTest extends TestCase
{
    // Test de la méthode `getRoles`
    public function testGetRoles(): void
    {
        // Création d'un utilisateur
        $utilisateur = new Utilisateur();

        // Attribution d'un rôle à l'utilisateur
        $utilisateur->setRoles(['ROLE_ADMIN']);

        // Vérification que les rôles retournés contiennent le rôle "ROLE_ADMIN"
        $roles = $utilisateur->getRoles();
        $this->assertContains('ROLE_ADMIN', $roles);

        // Vérification que "ROLE_USER" est toujours inclus par défaut
        $this->assertContains('ROLE_USER', $roles);
    }

    // Test de la méthode `setPassword`
    public function testHashedPassword(): void
    {
        // Création d'un utilisateur
        $utilisateur = new Utilisateur();
        $plainPassword = 'plain_password';

        // Définir le mot de passe en clair
        $utilisateur->setPlainPassword($plainPassword);

        // Appeler la méthode `setPassword` pour hacher le mot de passe
        $utilisateur->setPassword($plainPassword);

        // Récupérer le mot de passe haché
        $hashedPassword = $utilisateur->getPassword();

        // Vérifier que le mot de passe haché ne correspond pas au mot de passe en clair
        $this->assertFalse(password_verify($plainPassword, $hashedPassword));
    }

    // Test de la méthode `eraseCredentials`
    public function testSetPlainPassword(): void
    {
        // Création d'un utilisateur avec un mot de passe en clair
        $utilisateur = new Utilisateur();
        $utilisateur->setPlainPassword('plain_password');

        // Vérifier que le mot de passe en clair est défini
        $this->assertEquals('plain_password', $utilisateur->getPlainPassword());
    }

    // Test de la méthode `setIdentifiant`
    public function testSetIdentifiant(): void
    {
        // Création d'un utilisateur
        $utilisateur = new Utilisateur();

        // Définition de l'identifiant
        $utilisateur->setIdentifiant('john_doe');

        // Vérification que l'identifiant a bien été défini
        $this->assertEquals('john_doe', $utilisateur->getIdentifiant());
    }

    // Test de la méthode `setNom` et `getNom`
    public function testSetNom(): void
    {
        // Création d'un utilisateur
        $utilisateur = new Utilisateur();

        // Définition du nom
        $utilisateur->setNom('Doe');

        // Vérification que le nom est correctement défini
        $this->assertEquals('Doe', $utilisateur->getNom());
    }
}
