<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Musique.php';

class MusiqueController
{
    public function createMusique(array $data): array
    {
        $errors = [];

        $musique = new Musique();
        $musique->titre = trim($data['titre'] ?? '');
        $musique->artiste = trim($data['artiste'] ?? '');
        $musique->cheminFichierMP3 = $data['cheminFichierMP3'] ?? '';
        $musique->imageCouverture = $data['imageCouverture'] ?? '';
        $musique->userID = $data['userID'] ?? null;
        $musique->anneePublication = $data['anneePublication'] ?? null;

        if (empty($musique->titre)) {
            $errors[] = "Titre requis.";
        }
        if (empty($musique->artiste)) {
            $errors[] = "Artiste requis.";
        }
        if (empty($musique->cheminFichierMP3)) {
            $errors[] = "Fichier MP3 requis.";
        }
        if (empty($musique->imageCouverture)) {
            $errors[] = "Image de couverture requise.";
        }

        if (empty($errors)) {
            $musique->create();
        }

        return $errors;
    }

    public function updateMusiqueStatus(int $musiqueID, string $status): void
    {
        $musique = Musique::findById($musiqueID);
        if ($musique) {
            $musique->statusMusique = $status;
            $musique->update();
        }
    }

    public function deleteMusique(int $musiqueID): void
    {
        $musique = Musique::findById($musiqueID);
        if ($musique) {
            $musique->delete();
        }
    }

    public function getAllMusiques(): array
    {
        return Musique::findAll();
    }

    public function getMusiqueById(int $id): ?Musique
    {
        return Musique::findById($id);
    }
}
