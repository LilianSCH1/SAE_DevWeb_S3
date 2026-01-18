<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/Categorie.php';

class CategorieController
{
    public function createCategorie(array $data): array
    {
        $errors = [];

        $categorie = new Categorie();
        $categorie->nomCategorie = trim($data['nomCategorie'] ?? '');
        $categorie->description = trim($data['description'] ?? '');

        if (empty($categorie->nomCategorie)) {
            $errors[] = "Nom de catégorie requis.";
        }

        if (empty($errors)) {
            $categorie->create();
        }

        return $errors;
    }

    public function updateCategorie(int $categorieID, array $data): void
    {
        $categorie = Categorie::findById($categorieID);
        if ($categorie) {
            $categorie->nomCategorie = trim($data['nomCategorie'] ?? $categorie->nomCategorie);
            $categorie->description = trim($data['description'] ?? $categorie->description);
            $categorie->update();
        }
    }

    public function deleteCategorie(int $categorieID): void
    {
        $categorie = Categorie::findById($categorieID);
        if ($categorie) {
            $categorie->delete();
        }
    }

    public function getAllCategories(): array
    {
        return Categorie::findAll();
    }

    public function getCategorieById(int $id): ?Categorie
    {
        return Categorie::findById($id);
    }
}
