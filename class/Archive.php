<?php
require_once __DIR__ . '/Database.php';

class Archive
{
    public ?int $archiveID = null;
    public string $typeContenu;
    public int $contenuID;
    public ?string $dateArchivage = null;

    public static function findById(int $id): ?Archive
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM archive WHERE ArchiveID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM archive ORDER BY DateArchivage DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $archives = [];
        foreach ($rows as $row) {
            $archives[] = self::fromRow($row);
        }
        return $archives;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO archive (TypeContenu, ContenuID)
            VALUES (?, ?)
        ");
        $stmt->execute([
            $this->typeContenu,
            $this->contenuID
        ]);
        $this->archiveID = (int)$pdo->lastInsertId();
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM archive WHERE ArchiveID = ?");
        $stmt->execute([$this->archiveID]);
    }

    private static function fromRow(array $row): Archive
    {
        $a = new Archive();
        $a->archiveID = (int)$row['ArchiveID'];
        $a->typeContenu = $row['TypeContenu'];
        $a->contenuID = (int)$row['ContenuID'];
        $a->dateArchivage = $row['DateArchivage'];
        return $a;
    }
}
