<?php
require_once __DIR__ . '/Database.php';

class Commentaire
{
    private ?int $commentaireID = null;
    private string $typeContenu;
    private ?int $userID;
    private string $commentaire;
    private ?string $dateCommentaire = null;
    private bool $is_offensive = false;
    private string $report_reason;

    public static function findById(int $id): ?Commentaire
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM commentaire WHERE CommentaireID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public static function findAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM commentaire ORDER BY DateCommentaire DESC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $commentaires = [];
        foreach ($rows as $row) {
            $commentaires[] = self::fromRow($row);
        }
        return $commentaires;
    }

    public function create(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO commentaire (TypeContenu, UserID, Commentaire, is_offensive, report_reason)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->typeContenu,
            $this->userID,
            $this->commentaire,
            $this->is_offensive ? 1 : 0,
            $this->report_reason
        ]);
        $this->commentaireID = (int)$pdo->lastInsertId();
    }

    public function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE commentaire
            SET Commentaire = ?, is_offensive = ?, report_reason = ?
            WHERE CommentaireID = ?
        ");
        $stmt->execute([
            $this->commentaire,
            $this->is_offensive ? 1 : 0,
            $this->report_reason,
            $this->commentaireID
        ]);
    }

    public function delete(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM commentaire WHERE CommentaireID = ?");
        $stmt->execute([$this->commentaireID]);
    }

    public static function fromRow(array $row): Commentaire
    {
        $c = new Commentaire();
        $c->commentaireID = (int)$row['CommentaireID'];
        $c->typeContenu = $row['TypeContenu'];
        $c->userID = (int)$row['UserID'];
        $c->commentaire = $row['Commentaire'];
        $c->dateCommentaire = $row['DateCommentaire'];
        $c->is_offensive = (bool)$row['is_offensive'];
        $c->report_reason = $row['report_reason'];
        return $c;
    }

    public function setTypeContenu(string $value): void { $this->typeContenu = $value; }
    public function getTypeContenu(): string { return $this->typeContenu; }

    public function setUserID(?int $value): void { $this->userID = $value; }
    public function getUserID(): ?int { return $this->userID; }

    public function setCommentaire(string $value): void { $this->commentaire = $value; }
    public function getCommentaire(): string { return $this->commentaire; }

    public function setIsOffensive(bool $value): void { $this->is_offensive = $value; }
    public function getIsOffensive(): bool { return $this->is_offensive; }

    public function setReportReason(string $value): void { $this->report_reason = $value; }
    public function getReportReason(): string { return $this->report_reason; }
}
