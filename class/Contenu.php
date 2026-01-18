<?php
require_once __DIR__ . '/Database.php';

class Contenu
{
    public ?int $contenuID = null;
    public string $contenuName;
    public string $contenuSubject;
    public string $lienMedia;
    public string $statutContenu = 'en_attente';
    public ?int $categorieID = null;
    public ?int $certifID = null;

    public static function findById(int $id): ?Contenu
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM contenu WHERE ContenuID = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return self::fromRow($row);
    }

    public function publierContenu(): void
    {
        $this->statutContenu = 'valide';
        $this->update();
    }

    public function modifierContenu(array $data): void
    {
        $this->contenuName = $data['contenuName'] ?? $this->contenuName;
        $this->contenuSubject = $data['contenuSubject'] ?? $this->contenuSubject;
        $this->lienMedia = $data['lienMedia'] ?? $this->lienMedia;
        $this->update();
    }

    public function supprimerContenu(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM contenu WHERE ContenuID = ?");
        $stmt->execute([$this->contenuID]);
    }

    private function update(): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE contenu SET ContenuName = ?, ContenuSubject = ?, LienMedia = ?, statutContenu = ? WHERE ContenuID = ?");
        $stmt->execute([$this->contenuName, $this->contenuSubject, $this->lienMedia, $this->statutContenu, $this->contenuID]);
    }

    private static function fromRow(array $row): Contenu
    {
        $c = new Contenu();
        $c->contenuID = (int)$row['ContenuID'];
        $c->contenuName = $row['ContenuName'];
        $c->contenuSubject = $row['ContenuSubject'];
        $c->lienMedia = $row['LienMedia'];
        $c->statutContenu = $row['statutContenu'];
        $c->categorieID = (int)$row['CategorieID'];
        $c->certifID = (int)$row['CertifID'];
        return $c;
    }
}
