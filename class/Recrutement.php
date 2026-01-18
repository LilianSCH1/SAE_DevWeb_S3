<?php
require_once __DIR__ . '/Database.php';

class Recrutement
{
    public ?int $recrutementID = null;
    public int $userID;
    public string $nom;
    public string $prenom;
    public int $age;
    public string $motivation;
    public ?string $photoIdentite = null;
    public string $status = 'en_attente';
    public ?string $dateSoumission = null;
    public ?string $dateDecision = null;
    public ?int $adminID = null;
    public ?string $screenshot = null;
    public ?string $instagram = null;
    public ?string $twitter = null;
    public ?string $facebook = null;
    public ?string $youtube = null;
    public ?string $spotify = null;
    public ?string $deezer = null;

    public static function findById(int $id): ?Recrutement
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM recrutement WHERE RecrutementID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM recrutement ORDER BY DateSoumission DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recrutements = [];
        foreach ($rows as $row) {
            $recrutements[] = self::fromRow($row);
        }
        return $recrutements;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO recrutement (UserID, Nom, Prenom, Age, Motivation, PhotoIdentite, Status, Screenshot, Instagram, Twitter, Facebook, Youtube, Spotify, Deezer)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->userID,
            $this->nom,
            $this->prenom,
            $this->age,
            $this->motivation,
            $this->photoIdentite,
            $this->status,
            $this->screenshot,
            $this->instagram,
            $this->twitter,
            $this->facebook,
            $this->youtube,
            $this->spotify,
            $this->deezer
        ]);
        $this->recrutementID = (int)$pdo->lastInsertId();
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE recrutement
            SET Status = ?, DateDecision = ?, AdminID = ?
            WHERE RecrutementID = ?
        ");
        $stmt->execute([
            $this->status,
            $this->dateDecision,
            $this->adminID,
            $this->recrutementID
        ]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM recrutement WHERE RecrutementID = ?");
        $stmt->execute([$this->recrutementID]);
    }

    private static function fromRow(array $row): Recrutement
    {
        $r = new Recrutement();
        $r->recrutementID = (int)$row['RecrutementID'];
        $r->userID = (int)$row['UserID'];
        $r->nom = $row['Nom'];
        $r->prenom = $row['Prenom'];
        $r->age = (int)$row['Age'];
        $r->motivation = $row['Motivation'];
        $r->photoIdentite = $row['PhotoIdentite'];
        $r->status = $row['Status'];
        $r->dateSoumission = $row['DateSoumission'];
        $r->dateDecision = $row['DateDecision'];
        $r->adminID = $row['AdminID'] ? (int)$row['AdminID'] : null;
        $r->screenshot = $row['Screenshot'];
        $r->instagram = $row['Instagram'];
        $r->twitter = $row['Twitter'];
        $r->facebook = $row['Facebook'];
        $r->youtube = $row['Youtube'];
        $r->spotify = $row['Spotify'];
        $r->deezer = $row['Deezer'];
        return $r;
    }
}
