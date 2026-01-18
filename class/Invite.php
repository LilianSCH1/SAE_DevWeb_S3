<?php
require_once __DIR__ . '/User.php';

class Invite extends User
{
    public ?string $ipAdress = null;

    public function __construct()
    {
        $this->role = 'invite';
    }

    public function creerCompte(array $data): void
    {
        $this->pseudo = $data['pseudo'] ?? '';
        $this->firstName = $data['firstName'] ?? '';
        $this->lastName = $data['lastName'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $this->create();
    }

    public function consulterResultats(): array
    {
        // Logic to view results
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resultat ORDER BY TotalVotes DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
