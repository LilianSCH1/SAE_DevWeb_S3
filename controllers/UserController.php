<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/User.php';

class UserController
{
    public function updateProfile(User $user, array $data): array
    {
        $errors = [];

        $user->pseudo    = trim($data['UserPseudo'] ?? $user->pseudo);
        $user->firstName = trim($data['UserName'] ?? $user->firstName);
        $user->lastName  = trim($data['UserSurname'] ?? $user->lastName);
        $email           = trim($data['UserMail'] ?? $user->email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        if (empty($errors)) {
            $existing = User::findByEmail($email);
            if ($existing && $existing->id !== $user->id) {
                $errors[] = "Cet email est déjà utilisé.";
            } else {
                $user->email = $email;
                $user->updateProfile();
                $_SESSION['user_email'] = $user->email;
            }
        }

        return $errors;
    }

    public function updatePassword(User $user, array $data): array
    {
        $errors = [];

        $current = $data['currentPassword'] ?? '';
        $new1    = $data['newPassword'] ?? '';
        $new2    = $data['confirmNewPassword'] ?? '';

        if ($new1 !== $new2) {
            $errors[] = "Les nouveaux mots de passe ne correspondent pas.";
        }

        if (empty($errors)) {
            if (!password_verify($current, $user->passwordHash)) {
                $errors[] = "Mot de passe actuel incorrect.";
            } else {
                $user->updatePassword($new1);
            }
        }

        return $errors;
    }

    public function deleteAccount(User $user): void
    {
        $user->delete();
        session_unset();
        session_destroy();
    }
}
