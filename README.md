# MyPulse

MyPulse est une application web de gestion et de vote pour des musiques, artistes et groupes. Ce dépôt contient l'ensemble du code source (PHP) et des ressources nécessaires pour déployer MyPulse en local ou sur un serveur LAMP/WAMP.

**Table des matières**

- **Description**
- **Fonctionnalités**
- **Prérequis**
- **Installation et configuration**
- **Base de données**
- **Utilisation**
- **Structure du projet & fichiers importants**
- **Sécurité & bonnes pratiques**
- **Dépannage**
- **Contribuer**
- **Licence & Crédits**

**Description**

MyPulse permet de proposer, gérer et voter pour des musiques, artistes et groupes. Les utilisateurs peuvent s'inscrire/se connecter, écouter des extraits, voter et rechercher du contenu. Des rôles (`basique`, `certifie`, `admin`) gèrent les droits (proposition, validation, suppression).

**Fonctionnalités**

- Gestion des musiques, artistes et groupes (création, modification, archivage pour les admins).
- Système de vote par utilisateur (token de vote) avec possibilité de supprimer son vote.
- Lecteur audio intégré pour écouter les musiques (bouton "Écouter").
- Recherche interactive pour musiques, artistes et groupes.
- Pages publiques : accueil, contact, politique de confidentialité, conditions d'utilisation.
- Authentification et gestion de compte (connexion classique + Google OAuth présent dans `login/`).
- Uploads organisés dans `create/uploads/` (couvertures, sons, profils).

**Prérequis**

- PHP 7.4+ (ou version compatible utilisée sur votre environnement)
- MySQL / MariaDB
- Composer (optionnel si les dépendances sont déjà présentes)
- Serveur local : WAMP, XAMPP, MAMP ou serveur LAMP
- Navigateur moderne pour l'interface (Chrome, Firefox, Edge)

**Installation et configuration**

1. Copier le dépôt dans le répertoire de votre serveur web (ex : `c:/wamp64/www/MyPulse`).
2. Si `vendor/` n'est pas présent ou si vous voulez mettre à jour les dépendances, exécutez :

```bash
composer install
```

3. Créer une base de données MySQL et importer le fichier SQL fourni : `database/mypulse.sql` (via phpMyAdmin ou `mysql` CLI).

4. Configurer la connexion à la base :

- Ouvrez [database/dbconnect.php](database/dbconnect.php) et adaptez les paramètres (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) à votre environnement.

5. Vérifiez les permissions d'écriture pour les dossiers d'upload : `create/uploads/artistes/`, `create/uploads/groupes/`, `create/uploads/musiques/`.

6. (Optionnel) Générez ou vérifiez les tokens si nécessaire via `database/generate_tokens.php`.

**Base de données**

- Le fichier `database/mypulse.sql` contient la structure et les données nécessaires.
- Les tables principales : `musique`, `artiste`, `groupe`, `utilisateur`, `vote`.
- `utilisateur` contient un champ `token` utilisé pour lier les votes à un utilisateur.

**Utilisation**

- Accédez à l'application via `http://localhost/SAE_DevWeb_S3/index/index.php` ou `http://localhost/SAE_DevWeb_S3/vote/voter.php` pour la page de vote.
- Pour proposer du contenu (seulement pour les `admin` et les `certifie`): connectez-vous puis utilisez les formulaires dans `create/creer_musique.php`, `create/creer_artiste.php`, `create/creer_groupe.php`.
- Les administrateurs peuvent supprimer les éléments directement depuis l'interface de vote (bouton corbeille visible pour `admin`).

**Recherche instantanée**

La recherche dans la barre de vote est désormais instantanée (requêtes AJAX) : le fichier d'API est `vote/search_ajax.php` et le script client est injecté dans `script/script.js`.

**Structure du projet & fichiers importants**

- `index/` : pages publiques (accueil, header, footer).
- `vote/` : pages de vote et partials (`voter.php`, `vote_cards_musique.php`, `vote_cards_artiste.php`, `vote_cards_groupe.php`, `search_ajax.php`).
- `create/` : formulaires de création et dossiers d'uploads.
- `login/` : pages de connexion, tableau de bord, intégration Google.
- `class/` : classes PHP réutilisables (ex: `Database.php`, `User.php`).
- `database/` : scripts SQL et utilitaires.
- `script/` et `style/` : assets frontend (JS/CSS).

Fichiers clés :

- [vote/voter.php](vote/voter.php) : page principale de vote et point d'inclusion des partials.
- [vote/vote_cards_musique.php](vote/vote_cards_musique.php) : rendu des cartes musiques.
- [vote/search_ajax.php](vote/search_ajax.php) : endpoint pour recherche instantanée.
- [database/dbconnect.php](database/dbconnect.php) : configuration de la DB.

**Sécurité & bonnes pratiques**

- Ne laissez jamais `database/dbconnect.php` avec des identifiants en clair sur un serveur public.
- Validez et nettoyez les entrées utilisateurs côté serveur (déjà partiellement en place avec `htmlspecialchars()` et requêtes préparées PDO).
- Restreignez les permissions des dossiers d'uploads et limitez les types de fichiers acceptés.

**Dépannage**

- 500 / erreurs PHP : activez l'affichage des erreurs en développement (`php.ini`) ou vérifiez les logs Apache/PHP.
- Problèmes de connexion DB : vérifiez les paramètres dans [database/dbconnect.php](database/dbconnect.php) et que le serveur MySQL tourne.
- Assets manquants : assurez-vous que `create/uploads/` contient les fichiers et que les chemins relatifs sont corrects.
- Problèmes d'upload : vérifiez les permissions des dossiers `create/uploads/` et la taille maximale des fichiers dans `php.ini`.
- Votes non enregistrés : assurez-vous que l'utilisateur est connecté et possède un token valide.

**Licence & Crédits**

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails (si présent).

**Crédits**
- Développé par HOJA Valentin et SCHMITT Lilian dans le cadre du projet SAE DevWeb S3.
- Icônes : [Source des icônes, ex. Flaticon ou créées par l'équipe].
- Polices : Raleway et Lavonia Font.
- Technologies : PHP, MySQL, HTML/CSS/JS, Composer.

Pour toute question, contactez mypulse.company@gmail.com.
