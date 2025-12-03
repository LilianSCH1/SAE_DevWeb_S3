<?php
require_once __DIR__ . '../../class/Database.php';

class User
{
    public ?int $id = null;
    public string $pseudo;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $passwordHash;
    public string $role = 'invité';
    public ?string $dateInscription = null;

    public static function findById(int $id): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT UserID, UserPseudo, UserName, UserSurname, UserMail, UserPassword, Role, DateInscription
            FROM utilisateur
            WHERE UserID = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findByEmail(string $email): ?User
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT UserID, UserPseudo, UserName, UserSurname, UserMail, UserPassword, Role, DateInscription
            FROM utilisateur
            WHERE UserMail = ?
        ");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO utilisateur (UserPseudo, UserName, UserSurname, UserMail, UserPassword, Role)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->pseudo,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->passwordHash,
            $this->role
        ]);
        $this->id = (int)$pdo->lastInsertId();
    }

    public function updateProfile(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE utilisateur
            SET UserPseudo = ?, UserName = ?, UserSurname = ?, UserMail = ?
            WHERE UserID = ?
        ");
        $stmt->execute([
            $this->pseudo,
            $this->firstName,
            $this->lastName,
            $this->email,
            $this->id
        ]);
    }

    public function updatePassword(string $newPlainPassword): void
    {
        $this->passwordHash = password_hash($newPlainPassword, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE utilisateur SET UserPassword = ? WHERE UserID = ?");
        $stmt->execute([$this->passwordHash, $this->id]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM utilisateur WHERE UserID = ?");
        $stmt->execute([$this->id]);
    }

    private static function fromRow(array $row): User
    {
        $u = new User();
        $u->id              = (int)$row['UserID'];
        $u->pseudo          = $row['UserPseudo'];
        $u->firstName       = $row['UserName'];
        $u->lastName        = $row['UserSurname'];
        $u->email           = $row['UserMail'];
        $u->passwordHash    = $row['UserPassword'];
        $u->role            = $row['Role'];
        $u->dateInscription = $row['DateInscription'] ?? null;
        return $u;
    }
}
