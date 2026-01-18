<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Artiste.php';

class ArtisteController
{
    public function createArtiste(array $data): array
    {
        $errors = [];

        $artiste = new Artiste();
        $artiste->nomArtiste = trim($data['nomArtiste'] ?? '');
        $artiste->nomReel = trim($data['nomReel'] ?? '');
        $artiste->biographieCourte = trim($data['biographieCourte'] ?? '');
        $artiste->cheminFichierMP3 = $data['cheminFichierMP3'] ?? '';
        $artiste->imageProfil = $data['imageProfil'] ?? '';
        $artiste->userID = $data['userID'] ?? null;
        $artiste->anneeNaissance = $data['anneeNaissance'] ?? null;

        if (empty($artiste->nomArtiste)) {
            $errors[] = "Nom d'artiste requis.";
        }
        if (empty($artiste->cheminFichierMP3)) {
            $errors[] = "Fichier MP3 requis.";
        }
        if (empty($artiste->imageProfil)) {
            $errors[] = "Image de profil requise.";
        }

        if (empty($errors)) {
            $artiste->create();
        }

        return $errors;
    }

    public function updateArtisteStatus(int $artisteID, string $status): void
    {
        $artiste = Artiste::findById($artisteID);
        if ($artiste) {
            $artiste->statusArtiste = $status;
            $artiste->update();
        }
    }

    public function deleteArtiste(int $artisteID): void
    {
        $artiste = Artiste::findById($artisteID);
        if ($artiste) {
            $artiste->delete();
        }
    }

    public function getAllArtistes(): array
    {
        return Artiste::findAll();
    }

    public function getArtisteById(int $id): ?Artiste
    {
        return Artiste::findById($id);
    }
}
