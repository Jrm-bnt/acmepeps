<?php

declare(strict_types=1);

namespace controllers;

use entities\User;
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
    public static function sigin(): void
    {
        //*Rendre la vue
        Router::render('sigin.php', ['log' => null]);
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
        Router::render('sigin.php', ['log' => $user->log, 'errors' => $errors]);
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
}