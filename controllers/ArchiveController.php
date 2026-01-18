<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Archive.php';

class ArchiveController
{
    public function createArchive(array $data): array
    {
        $errors = [];

        $archive = new Archive();
        $archive->typeContenu = $data['typeContenu'] ?? '';
        $archive->contenuID = $data['contenuID'] ?? null;

        if (empty($archive->typeContenu)) {
            $errors[] = "Type de contenu requis.";
        }
        if (!$archive->contenuID) {
            $errors[] = "ID du contenu requis.";
        }

        if (empty($errors)) {
            $archive->create();
        }

        return $errors;
    }

    public function deleteArchive(int $archiveID): void
    {
        $archive = Archive::findById($archiveID);
        if ($archive) {
            $archive->delete();
        }
    }

    public function getAllArchives(): array
    {
        return Archive::findAll();
    }

    public function getArchiveById(int $id): ?Archive
    {
        return Archive::findById($id);
    }
}
