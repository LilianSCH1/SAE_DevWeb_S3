<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Commentaire.php';

class CommentaireController
{
    public function createCommentaire(array $data): array
    {
        $errors = [];

        $commentaire = new Commentaire();
        $commentaire->setTypeContenu($data['typeContenu'] ?? 'general');
        $commentaire->setUserID($data['userID'] ?? null);
        $commentaire->setCommentaire(trim($data['commentaire'] ?? ''));

        if (!$commentaire->getUserID()) {
            $errors[] = "ID utilisateur requis.";
        }
        if (empty($commentaire->getCommentaire())) {
            $errors[] = "Commentaire requis.";
        }

        if (empty($errors)) {
            $commentaire->create();
        }

        return $errors;
    }

    public function updateCommentaire(int $commentaireID, array $data): void
    {
        $commentaire = Commentaire::findById($commentaireID);
        if ($commentaire) {
            $commentaire->setCommentaire(trim($data['commentaire'] ?? $commentaire->getCommentaire()));
            $commentaire->setIsOffensive($data['is_offensive'] ?? $commentaire->getIsOffensive());
            $commentaire->setReportReason($data['report_reason'] ?? $commentaire->getReportReason());
            $commentaire->update();
        }
    }

    public function deleteCommentaire(int $commentaireID): void
    {
        $commentaire = Commentaire::findById($commentaireID);
        if ($commentaire) {
            $commentaire->delete();
        }
    }

    public function getAllCommentaires(): array
    {
        return Commentaire::findAll();
    }

    public function getCommentaireById(int $id): ?Commentaire
    {
        return Commentaire::findById($id);
    }

    public function getCommentairesByType(string $typeContenu): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM commentaire WHERE TypeContenu = ? ORDER BY DateCommentaire DESC");
        $stmt->execute([$typeContenu]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $commentaires = [];
        foreach ($rows as $row) {
            $commentaires[] = Commentaire::fromRow($row);
        }
        return $commentaires;
    }
}
