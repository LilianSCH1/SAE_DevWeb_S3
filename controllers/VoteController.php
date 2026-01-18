<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Vote.php';

class VoteController
{
    public function enregistrerVote(array $data): array
    {
        $errors = [];

        $vote = new Vote();
        $vote->candidatID = $data['candidatID'] ?? null;
        $vote->userID = $data['userID'] ?? null;

        if (!$vote->candidatID) {
            $errors[] = "ID du candidat requis.";
        }

        if (empty($errors)) {
            $vote->enregistrerVote();
        }

        return $errors;
    }

    public function calculerResultat(int $candidatID): array
    {
        $vote = new Vote();
        $vote->candidatID = $candidatID;
        return $vote->calculerResultat();
    }

    public function getVotesByType(string $typeContenu): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM vote WHERE TypeContenu = ? ORDER BY DateVote DESC");
        $stmt->execute([$typeContenu]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
