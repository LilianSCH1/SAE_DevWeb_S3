<?php
require_once __DIR__ . '/Database.php';

class Vote
{
    public ?int $voteID = null;
    public ?int $candidatID = null;
    public ?string $dateVote = null;
    public ?int $userID = null;

    public static function findById(int $id): ?Vote
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM vote WHERE VoteID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public function enregistrerVote(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("INSERT INTO vote (CandidatID, DateVote, ValeurVote, Token) VALUES (?, NOW(), 1, ?)");
        $stmt->execute([$this->candidatID, session_id()]);
        $this->voteID = (int)$pdo->lastInsertId();
    }

    public function calculerResultat(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM vote WHERE CandidatID = ?");
        $stmt->execute([$this->candidatID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private static function fromRow(array $row): Vote
    {
        $v = new Vote();
        $v->voteID = (int)$row['VoteID'];
        $v->candidatID = (int)$row['ContenuID'];
        $v->dateVote = $row['DateVote'];
        // Assuming Token is used to link to user
        return $v;
    }
}
