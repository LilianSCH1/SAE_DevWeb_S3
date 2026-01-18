<?php
require_once __DIR__ . '/User.php';

class Administrateur extends User
{
    public ?int $adminID = null;
    public ?string $adminName = null;
    public ?string $adminSurname = null;
    public ?string $adminMail = null;
    public ?int $adminPhone = null;
    public ?string $adminPassword = null;

    public function __construct()
    {
        $this->role = 'admin';
    }

    public static function findById(int $id): ?Administrateur
    {
        $user = parent::findById($id);
        if (!$user || $user->role !== 'admin') return null;
        return self::fromUser($user);
    }

    public static function findByEmail(string $email): ?Administrateur
    {
        $user = parent::findByEmail($email);
        if (!$user || $user->role !== 'admin') return null;
        return self::fromUser($user);
    }

    public function gererCandidats(): array
    {
        // Logic to manage candidates
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM recrutement WHERE Status = 'en_attente'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private static function fromUser(User $user): Administrateur
    {
        $a = new Administrateur();
        $a->id = $user->id;
        $a->pseudo = $user->pseudo;
        $a->firstName = $user->firstName;
        $a->lastName = $user->lastName;
        $a->email = $user->email;
        $a->passwordHash = $user->passwordHash;
        $a->role = $user->role;
        $a->token = $user->token;
        $a->dateInscription = $user->dateInscription;
        // Load additional fields if stored separately
        return $a;
    }
}
