<?php
require_once __DIR__ . '/Database.php';

class Musique
{
    public ?int $musiqueID = null;
    public string $titre;
    public string $artiste;
    public string $cheminFichierMP3;
    public string $imageCouverture;
    public string $statusMusique = 'en_attente';
    public ?int $userID = null;
    public ?string $dateProposition = null;
    public ?int $anneePublication = null;
    public int $nombreVotes = 0;

    public static function findById(int $id): ?Musique
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM musique WHERE MusiqueID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM musique ORDER BY DateProposition DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $musiques = [];
        foreach ($rows as $row) {
            $musiques[] = self::fromRow($row);
        }
        return $musiques;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO musique (Titre, Artiste, CheminFichierMP3, ImageCouverture, StatusMusique, UserID, AnneePublication)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->titre,
            $this->artiste,
            $this->cheminFichierMP3,
            $this->imageCouverture,
            $this->statusMusique,
            $this->userID,
            $this->anneePublication
        ]);
        $this->musiqueID = (int)$pdo->lastInsertId();
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE musique
            SET Titre = ?, Artiste = ?, CheminFichierMP3 = ?, ImageCouverture = ?, StatusMusique = ?, AnneePublication = ?, NombreVotes = ?
            WHERE MusiqueID = ?
        ");
        $stmt->execute([
            $this->titre,
            $this->artiste,
            $this->cheminFichierMP3,
            $this->imageCouverture,
            $this->statusMusique,
            $this->anneePublication,
            $this->nombreVotes,
            $this->musiqueID
        ]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM musique WHERE MusiqueID = ?");
        $stmt->execute([$this->musiqueID]);
    }

    private static function fromRow(array $row): Musique
    {
        $m = new Musique();
        $m->musiqueID = (int)$row['MusiqueID'];
        $m->titre = $row['Titre'];
        $m->artiste = $row['Artiste'];
        $m->cheminFichierMP3 = $row['CheminFichierMP3'];
        $m->imageCouverture = $row['ImageCouverture'];
        $m->statusMusique = $row['StatusMusique'];
        $m->userID = $row['UserID'] ? (int)$row['UserID'] : null;
        $m->dateProposition = $row['DateProposition'];
        $m->anneePublication = $row['AnneePublication'] ? (int)$row['AnneePublication'] : null;
        $m->nombreVotes = (int)$row['NombreVotes'];
        return $m;
    }
}
