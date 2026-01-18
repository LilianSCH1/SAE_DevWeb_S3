<?php
require_once __DIR__ . '/Database.php';

class Artiste
{
    public ?int $artisteID = null;
    public string $nomArtiste;
    public ?string $nomReel = null;
    public ?string $biographieCourte = null;
    public string $cheminFichierMP3;
    public string $imageProfil;
    public string $statusArtiste = 'en_attente';
    public ?int $userID = null;
    public ?string $dateProposition = null;
    public ?int $anneeNaissance = null;
    public int $nombreVotes = 0;

    public static function findById(int $id): ?Artiste
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM artiste WHERE ArtisteID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM artiste ORDER BY DateProposition DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $artistes = [];
        foreach ($rows as $row) {
            $artistes[] = self::fromRow($row);
        }
        return $artistes;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO artiste (NomArtiste, NomReel, BiographieCourte, CheminFichierMP3, ImageProfil, StatusArtiste, UserID, AnneeNaissance)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->nomArtiste,
            $this->nomReel,
            $this->biographieCourte,
            $this->cheminFichierMP3,
            $this->imageProfil,
            $this->statusArtiste,
            $this->userID,
            $this->anneeNaissance
        ]);
        $this->artisteID = (int)$pdo->lastInsertId();
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE artiste
            SET NomArtiste = ?, NomReel = ?, BiographieCourte = ?, CheminFichierMP3 = ?, ImageProfil = ?, StatusArtiste = ?, AnneeNaissance = ?, NombreVotes = ?
            WHERE ArtisteID = ?
        ");
        $stmt->execute([
            $this->nomArtiste,
            $this->nomReel,
            $this->biographieCourte,
            $this->cheminFichierMP3,
            $this->imageProfil,
            $this->statusArtiste,
            $this->anneeNaissance,
            $this->nombreVotes,
            $this->artisteID
        ]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM artiste WHERE ArtisteID = ?");
        $stmt->execute([$this->artisteID]);
    }

    private static function fromRow(array $row): Artiste
    {
        $a = new Artiste();
        $a->artisteID = (int)$row['ArtisteID'];
        $a->nomArtiste = $row['NomArtiste'];
        $a->nomReel = $row['NomReel'];
        $a->biographieCourte = $row['BiographieCourte'];
        $a->cheminFichierMP3 = $row['CheminFichierMP3'];
        $a->imageProfil = $row['ImageProfil'];
        $a->statusArtiste = $row['StatusArtiste'];
        $a->userID = $row['UserID'] ? (int)$row['UserID'] : null;
        $a->dateProposition = $row['DateProposition'];
        $a->anneeNaissance = $row['AnneeNaissance'] ? (int)$row['AnneeNaissance'] : null;
        $a->nombreVotes = (int)$row['NombreVotes'];
        return $a;
    }
}
