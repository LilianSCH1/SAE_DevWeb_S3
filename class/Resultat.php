<?php
require_once __DIR__ . '/Database.php';

class Resultat
{
    public ?int $resultatID = null;
    public string $resultatName;
    public ?string $dateMAJ = null;
    public ?int $categorieID = null;

    public static function findById(int $id): ?Resultat
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resultat WHERE ResultatID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public function afficherClassement(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resultat WHERE CategorieID = ? ORDER BY TotalVotes DESC");
        $stmt->execute([$this->categorieID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function modifierClassement(): void
    {
        // Logic to modify ranking
    }

    public function supprimerClassement(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM resultat WHERE ResultatID = ?");
        $stmt->execute([$this->resultatID]);
    }

    private static function fromRow(array $row): Resultat
    {
        $r = new Resultat();
        $r->resultatID = (int)$row['ResultatID'];
        $r->resultatName = $row['ResultatName'];
        $r->dateMAJ = $row['DateCalcul'];
        $r->categorieID = (int)$row['TypeContenu']; // Assuming mapping
        return $r;
    }
}
