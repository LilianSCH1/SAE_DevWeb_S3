<?php
require_once __DIR__ . '/Database.php';

class Categorie
{
    public ?int $categorieID = null;
    public string $nomCategorie;
    public ?string $description = null;

    public static function findById(int $id): ?Categorie
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categorie WHERE CategorieID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM categorie ORDER BY NomCategorie ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $categories = [];
        foreach ($rows as $row) {
            $categories[] = self::fromRow($row);
        }
        return $categories;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO categorie (NomCategorie, Description)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $this->nomCategorie,
            $this->description
        ]);
        $this->categorieID = (int)$pdo->lastInsertId();
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE categorie
            SET NomCategorie = ?, Description = ?
            WHERE CategorieID = ?
        ");
        $stmt->execute([
            $this->nomCategorie,
            $this->description,
            $this->categorieID
        ]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM categorie WHERE CategorieID = ?");
        $stmt->execute([$this->categorieID]);
    }

    public function listerCandidats(): array
    {
        // Logic to list candidates, e.g., from musique, artiste, groupe
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM musique WHERE StatusMusique = 'classement' UNION SELECT * FROM artiste WHERE StatusArtiste = 'classement' UNION SELECT * FROM groupe WHERE StatusGroupe = 'classement'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function ajouterCandidat(MembreCertifie $candidat): void
    {
        // Logic to add candidate
    }

    private static function fromRow(array $row): Categorie
    {
        $c = new Categorie();
        $c->categorieID = (int)$row['CategorieID'];
        $c->nomCategorie = $row['NomCategorie'];
        $c->description = $row['Description'];
        return $c;
    }
}
