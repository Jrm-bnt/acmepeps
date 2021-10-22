<?php

declare(strict_types=1);


namespace peps\core;

use SessionHandlerInterface;


/**
 * Gestion des sessions en DB.
 * NECESSITE une table "session" avec les colonnes "sid", "data", "dateSession".
 * 3 modes possibles : 
 *      PERSISTENT: la session se termine exclusivement après l'expiration du timeout au-delà de la DERNIERE requête du client.
 *      HYBRID: La session se termine à la fermeture du navigateur OU après l'expiration du timeout au-delà de la dernière requête du client. 
 *      ABSOLUTE: La session se termine exclusivement après l'expiration du timeout au-delà de la PREMIÈRE requête du client.
 */
class SessionDB implements SessionHandlerInterface
{


    /**
     * Initialise et démarre la session.
     *
     * @param integer $timeout Durée maxi de la session (en secondes)
     * @param string $mode Mode de la session. (PERSISTENT | HYBRIDE | ABSOLUTE)
     * @param string $sameSite Mitigation CSRF. 
     */
    public static function init(int $timeout, string $mode, string $sameSite): void
    {
        //* Définir la durée de vie du cookie.
        match ($mode) {
            Cfg::get('SESSION_PERSISTENT') => ini_set('session.cookie_lifetime', (string)(86400 * 365 * 20)), //* 20ans (expiration gérée coté serveur).

            Cfg::get('SESSION_HYBRID') => ini_set('session.cookie_lifetime', '0'), //* cookie de session '0' = mis en RAM

            Cfg::get('SESSION_ABSOLUTE') => ini_set('session.cookie_lifetime', (string) $timeout), //* Cookie à durée limitée au timeout.

        };
        //* Définir le timeout GC pour supprimer les sessions expirées.
        ini_set('session.gc_maxlifetime', (string) $timeout);
        //* Utiliser les cookies.
        ini_set('session.use_cookies', '1');
        //* Utiliser seulement les cookies.
        ini_set('session.use_only_cookies', '1');
        //* Ne pas passer l'ID de session en GET.
        ini_set('session.trans_sid', '0');
        //* Mitiger les attaques XSS (Cross Site scripting = injections) en interdisant l'accès aux cookies via JS.
        ini_set('session.cookie_httponly', '1');





        session_set_save_handler(new self());
        session_start();
    }

    public function open($path, $name): bool
    {
        var_dump('Open');
        return true;
    }

    public function read($id): string|false
    {
        var_dump("read {$id}");
        return '';
    }

    public function write($id, $data): bool
    {

        var_dump("write {$id}: {$data}");
        return true;
    }


    public function close(): bool
    {
        var_dump('Close');
        return true;
    }


    public function destroy($id): bool
    {
        var_dump("destroy {$id}");
        return true;
    }


    public function gc($maxlifetime): int|false
    {
        var_dump('GarbageCol {$maxlifetime}');

        return 3;
    }
}