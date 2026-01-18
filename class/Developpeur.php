<?php
require_once __DIR__ . '/User.php';

class Developpeur extends User
{
    public ?int $devID = null;
    public ?string $devName = null;
    public ?string $devSurname = null;
    public ?string $devMail = null;
    public ?string $competence = null;

    public function __construct()
    {
        $this->role = 'developpeur'; // Assuming a role for developer
    }

    public static function findById(int $id): ?Developpeur
    {
        $user = parent::findById($id);
        if (!$user || $user->role !== 'developpeur') return null;
        return self::fromUser($user);
    }

    public static function findByEmail(string $email): ?Developpeur
    {
        $user = parent::findByEmail($email);
        if (!$user || $user->role !== 'developpeur') return null;
        return self::fromUser($user);
    }

    public function gererFrontend(): void
    {
        // Logic for managing frontend
    }

    public function gererBackend(): void
    {
        // Logic for managing backend
    }

    public function creerCategorie(string $nom, string $description): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO categorie (NomCategorie, Description) VALUES (?, ?)");
        $stmt->execute([$nom, $description]);
    }

    public function maintenirSecurite(): void
    {
        // Logic for maintaining security
    }

    private static function fromUser(User $user): Developpeur
    {
        $dev = new Developpeur();
        $dev->id = $user->id;
        $dev->pseudo = $user->pseudo;
        $dev->firstName = $user->firstName;
        $dev->lastName = $user->lastName;
        $dev->email = $user->email;
        $dev->passwordHash = $user->passwordHash;
        $dev->role = $user->role;
        $dev->token = $user->token;
        $dev->dateInscription = $user->dateInscription;
        // Load additional fields if stored separately
        return $dev;
    }
}
