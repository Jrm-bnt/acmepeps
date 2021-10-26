<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Interface de connexion des utilisateurs.
 */
interface UserLoggable extends ORM
{
    /**
     * Tente de loguer le UserLoggable.
     *
     * @return boolean True ou False selon que le UserLoggable a été logué ou pas.
     */
    function login(): bool;

    /**
     * Retourne le UserLoggable en session ou null
     *
     * @return self|null
     */
    static function getUserSession(): ?self;
}