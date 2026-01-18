<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Resultat.php';

class ResultatController
{
    public function afficherClassement(int $categorieID): array
    {
        $resultat = new Resultat();
        $resultat->categorieID = $categorieID;
        return $resultat->afficherClassement();
    }

    public function modifierClassement(int $resultatID, array $data): void
    {
        $resultat = Resultat::findById($resultatID);
        if ($resultat) {
            $resultat->resultatName = $data['resultatName'] ?? $resultat->resultatName;
            $resultat->dateMAJ = date('Y-m-d H:i:s');
            // Assuming update method exists
            // $resultat->update();
        }
    }

    public function supprimerClassement(int $resultatID): void
    {
        $resultat = Resultat::findById($resultatID);
        if ($resultat) {
            $resultat->supprimerClassement();
        }
    }

    public function getAllResultats(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM resultat ORDER BY DateCalcul DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
