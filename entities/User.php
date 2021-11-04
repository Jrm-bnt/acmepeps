<?php

declare(strict_types=1);

namespace entities;

use peps\core\DBAL;
use peps\core\ORMDB;
use peps\core\UserLoggable;
use peps\core\Validator;

/**
 * Entité User.
 * 
 * @see ORMDB
 * @see UserLoggable
 */
class User extends ORMDB implements UserLoggable, Validator
{
    //*Message d'erreur

    protected const ERR_INVALID_LOG = "Identifiant invalide";
    protected const ERR_INVALID_LASTNAME = "Nom invalide";
    protected const ERR_INVALID_FIRSTNAME = "Prénom invalide";
    protected const ERR_INVALID_EMAIL = "Email invalide";
    protected const ERR_INVALID_PWD = "Mot de passe invalide";


    /**
     * PK.
     */
    public ?int $idUser = null;

    /**
     * Identifiant de connexion.
     */
    public ?string $log = null;

    /**
     * Mot de passe de connexion.
     * En clair après saisie.
     * Chiffré après hydratation.
     */
    public ?string $pwd = null;

    /**
     * Nom.
     */
    public ?string $lastName = null;

    /**
     * Prénom.
     */
    public ?string $firstName = null;

    /**
     * Email.
     */
    public ?string $email = null;

    /**
     * Hash pour réinitialisation du mote de passe
     */
    public ?string $pwdHash = null;

    /**
     * Timeout pour réinitialisation du mote de passe
     */
    public ?string $pwdTimeout = null;

    /**
     * Instance de l'utilisateur en session.
     * En cache pour lazy loading.
     */
    protected static ?self $userSession = null;



    /**
     * Constructeur.
     */
    public function __construct(int $idUser = null)
    {
        $this->idUser = $idUser;
    }

    /**
     * {@inheritDoc}
     */
    public function login(): bool
    {
        // Si log ou pwd non renseignées, retourner false.
        if (!$this->log || !$this->pwd)
            return false;
        // Si aucun utilisateur correspondant au login, retourner false.
        if (!$user = self::findOneBy(['log' => $this->log]))
            return false;
        // Si mot de passe incorrect, retourner false.
        if (!password_verify($this->pwd, $user->pwd))
            return false;
        // Inscrire l'utilisateur dans la session et retourner true.
        $_SESSION['idUser'] = $user->idUser;
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public static function getUserSession(): ?self
    {
        // Si pas en cache, créer et hydrater l'utilisateur en session.
        if (!self::$userSession) {
            // Créer une instance.
            $user = new self($_SESSION['idUser'] ?? null);
            // Si $user non null et hydratation réussie, stocker l'instance dans le cache.
            self::$userSession = $user && $user->hydrate() ? $user : null;
        }
        // Retourner l'utilisateur en session.
        return self::$userSession;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function validate(?array &$errors = []): bool
    {
        //* Flag
        $valid = true;


        //* Vérifier l'identifiant' (obligatoire, unique et max 10 caractères).
        if (!$this->log || mb_strlen($this->log) > 10 || User::findOneBy(['log' => $this->log])) {
            $valid = false;
            $errors[] = self::ERR_INVALID_LOG;
        }

        //* Vérifier le nom' (obligatoire et max 30 caractères).
        if (!$this->lastName || mb_strlen($this->lastName) > 30) {
            $valid = false;
            $errors[] = self::ERR_INVALID_LASTNAME;
        }
        //* Vérifier le prénom' (obligatoire et max 20 caractères).
        if (!$this->firstName || mb_strlen($this->firstName) > 30) {
            $valid = false;
            $errors[] = self::ERR_INVALID_FIRSTNAME;
        }
        //* Vérifier l'email'' (obligatoire,unique, véritable email et max 50 caractères).
        if (!$this->email || mb_strlen($this->email) > 50 || User::findOneBy(['email' => $this->email])) {
            $valid = false;
            $errors[] = self::ERR_INVALID_EMAIL;
        }
        //* Vérifier le mot de passe (obligatoire et max 10 caractères).
        if (!$this->pwd || mb_strlen($this->pwd) > 50) {
            $valid = false;
            $errors[] = self::ERR_INVALID_PWD;
        }




        return $valid;
    }
}