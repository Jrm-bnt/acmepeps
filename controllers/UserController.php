<?php

declare(strict_types=1);

namespace controllers;

use entities\User;
use peps\core\ORMDB;
use peps\core\Router;

/**
 * Contrôle la connexion/déconnexion des utilisateurs.
 * 
 * @see User
 * @see Router
 */
final class UserController
{
    //* Messages d'erreur.
    private const ERR_LOGIN = "Identifiant ou mot de passe absents ou invalides";
    private const ERR_INVALID_LOG = "Identifiant ou mot de passe absents ou invalides";
    private const ERR_INVALID_HASH = "Lien invalide ou expiré.";


    /**
     * Constructeur privé.
     */
    private function __construct()
    {
    }

    /**
     * Affiche le formulaire de connexion.
     * 
     * GET user/sigin
     */
    public static function signin(): void
    {
        //*Rendre la vue
        Router::render('signin.php', ['log' => null]);
    }

    /**
     * Connecte l'utilisateur si possible puis redirige.
     * 
     * POST user/login
     */
    public static function login(): void
    {
        //* Prévoir le tableau des messages d'erreur.
        $errors = [];

        //* Instancier un utilisateur.
        $user = new User();

        //* Récuperer les données POST.
        $user->log = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING) ?: null;
        $user->pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING) ?: null;

        //* Si login OK, rediriger vers l'accueil.
        if ($user->login())
            Router::redirect('/');

        //* Sinon, afficher de nouveau le formulaire avec le message d'erreur.
        $errors[] = self::ERR_LOGIN;
        Router::render('signin.php', ['log' => $user->log, 'errors' => $errors]);
    }


    /**
     * Déconnecte l'utilisateur puis redirige.
     * 
     *GET user/logout
     */
    public static function logout(): void
    {
        //* Détruire la session.
        session_destroy();
        //* Rediriger vers l'accueil.
        Router::redirect('/');
    }

    /**
     * Affiche la vue du mot de passe oublié
     *
     * GET /user/forgottenPwd
     */
    public static function forgottenPwd(): void
    {
        Router::render('forgottenPwd.php', ['log' => null]);
    }



    /**
     * Génère et envoie par email un lien de destiné à saisir un nouveau mot de passe
     *
     * GET /user/newPwd/{hash}
     */
    public static function newPwd(): void
    {
        //* Initialiser le tableau des messages d'erreur.
        $errors = [];
        //* Récupérer 'log'.
        $log = filter_input(INPUT_POST, 'log', FILTER_SANITIZE_STRING) ?: null;
        //* Si log inconnu, rendre la vue du 'forgottenPwd'
        if (!$user = User::findOneBy(['log' => $log])) {
            $errors[] = self::ERR_INVALID_LOG;
            Router::render('forgottenPwd.php', ['log' => $log, 'errors' => $errors]);
        }

        //* Générer un hash.
        $hash = hash('sha1', microtime(), false);
        //* Stocker le hash et son timeout en DB.
        $user->pwdHash = $hash;
        $user->pwdTimeout = date('Y-m-d H:i:s', time() + 10 * 60);  //* 10 minutes
        $user->persist();
        //* Envoyer le lien par email.
        $subject = "ACME : Réinitialiser votre mot de passe";
        $body = "Bonjour, 
        Cliquez sur le lien  ci-dessous pour réinitialiser votre mot de passe.
        Ce lien expire dans 10 minutes.
        
        http://acmepeps/user/setPwd/{$hash}";

        mail($user->email, $subject, $body);
        //* Rediriger vers la vue de connexion.
        Router::redirect('/');
    }

    /**
     * Affiche la vue permettant de saisir un nouveau mot de passe.
     *
     *GET /user/setPwd{hash}
     *
     * @param array $params Tableau des paramètres
     */
    public static function setPwd(array $params): void
    {

        //* Récupérer le hachage.
        $hash = $params['hash'];
        //* Si 'hash' absent ou inconnu ou timeout expiré, reprendre la vue 'forgottenPwd'.
        if (!$hash || !($user = User::findOneBy(['pwdHash' => $hash])) || $user->pwdTimeout < date('Y-m-d H:i:s')) {
            $errors[] = self::ERR_INVALID_HASH;
            Router::render('forgottenPwd.php', ['log' => null, 'errors' => $errors]);
        }

        //* Rendre la vue.
        Router::render('setPwd.php', ['hash' => $hash]);
    }

    public static function savePwd(): void
    {
        //* Récupère nouveau mot de passe et hachage.
        $pwd = filter_input(INPUT_POST, 'pwd', FILTER_SANITIZE_STRING) ?: null;
        $hash = filter_input(INPUT_POST, 'hash', FILTER_SANITIZE_STRING) ?: null;

        //* Si 'pwd' absent ou 'hash' absent ou inconnu ou timeout expiré, reprendre la vue 'forgottenPwd'.
        if (!$pwd || !$hash || !($user = User::findOneBy(['pwdHash' => $hash])) || $user->pwdTimeout < date('Y-m-d H:i:s')) {
            $errors[] = self::ERR_INVALID_HASH;
            Router::render('forgottenPwd.php', ['log' => null, 'errors' => $errors]);
        }
        //* Chiffrer le nouveau mot de passe
        $user->pwd = password_hash($pwd, PASSWORD_DEFAULT);
        //* Supprimer le hash et le timeout
        $user->pwdhash = null;
        $user->pwdTimeout = null;
        //* Persister
        $user->persist();

        //* Inscrire l'utilisateur dans la session.
        $_SESSION['idUser'] = $user->idUser;

        //* Envoyer le lien par email.
        $subject = "ACME :Changement réussi de votre mot de passe";
        $body = "Bonjour, 
Votre mot de passe vient d'être modifié.";
        mail($user->email, $subject, $body);

        //* Rediriger vers l'accueil
        Router::redirect('/');
    }
}