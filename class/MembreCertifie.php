<?php
require_once __DIR__ . '/User.php';

class MembreCertifie extends User
{
    public ?int $certifID = null;
    public ?string $reseauSocial = null;
    public ?string $dateVerif = null;
    public string $hashtagVerification = "#JustJoinedMyPulse";
    public string $statutVerification = 'en_attente';

    public function __construct()
    {
        $this->role = 'certifie';
    }

    public static function findById(int $id): ?MembreCertifie
    {
        $user = parent::findById($id);
        if (!$user || $user->role !== 'certifie') return null;
        return self::fromUser($user);
    }

    public static function findByEmail(string $email): ?MembreCertifie
    {
        $user = parent::findByEmail($email);
        if (!$user || $user->role !== 'certifie') return null;
        return self::fromUser($user);
    }

    public function create(): void
    {
        parent::create();
        // Additional logic for certification if needed
    }

    public function proposerContenu(array $data): void
    {
        // Logic to propose content, e.g., insert into musique, artiste, groupe tables
        // This would depend on the type
    }

    private static function fromUser(User $user): MembreCertifie
    {
        $mc = new MembreCertifie();
        $mc->id = $user->id;
        $mc->pseudo = $user->pseudo;
        $mc->firstName = $user->firstName;
        $mc->lastName = $user->lastName;
        $mc->email = $user->email;
        $mc->passwordHash = $user->passwordHash;
        $mc->role = $user->role;
        $mc->token = $user->token;
        $mc->dateInscription = $user->dateInscription;
        // Load additional fields if stored separately
        return $mc;
    }
}
