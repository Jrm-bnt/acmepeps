<?php

declare(strict_types=1);


namespace peps\core;

use PDO;
use PDOException;
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
     * Vrai si session périmée, faux sinon
     */
    protected static bool $expired = false;

    /**
     * Undocumented variableDurée maxi de la session (secondes).
     *
     * @var integer
     */
    protected static int $timeout;


    /**
     * Initialise et démarre la session.
     *
     * @param integer $timeout Durée maxi de la session (en secondes)
     * @param string $mode Mode de la session. (PERSISTENT | HYBRIDE | ABSOLUTE)
     * @param string $sameSite Mitigation CSRF. 
     */
    public static function init(int $timeout, string $mode, string $sameSite): void
    {
        //* Définir le timeout.
        self::$timeout = $timeout;

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
        //* Mitiger les attaques SFA (Session Site Attack) en refusant les cookies non générés par PHP.
        ini_set('session.use_strict_mode', '1');
        //* Mitiger les attaques CSRF (Cross Site Request Forgery)
        ini_set('session.cookie_samesite', '1');
        //* Définir une instance de cette classe comme gestionnaire des sessions.
        session_set_save_handler(new self());
        //* Démarrer la session.
        session_start();
        //* Si session expirée, la détruire et en démarrer une nouvelle.
        if (self::$expired) {
            session_destroy();
            self::$expired = false;
            session_start();
        }
    }
    /**
     * Inutile ici.
     *
     * @param string $path Chemin du fichier de sauvegarde de la session.
     * @param string $name Nom de la session. (PHPSESSID par défaut)
     * @return boolean Pour un usage interne PHP, ici systématiquement true.
     */
    public function open($path, $name): bool
    {
        return true;
    }
    /**
     * Lire et retourner les données de session.
     *
     * @param string $id SID. 
     * @return string|false Données de session sérialisées (PHP) ou false si lecture impossible.
     */
    public function read($id): string|false
    {
        //* Créer la requête.
        $q = "SELECT * FROM session WHERE sid = :sid";
        $params = [':sid' => $id];
        //*Exécuter la requête et si une session est retrouvée, vérifier sa validité.
        if ($objSession = DBAL::get()->xeq($q, $params)->findOne()) {
            //* Si expirée, passer le booléen $expired à true et retourner une chaîne vide.
            if (strtotime($objSession->dateSession) + self::$timeout < time()); {
                self::$expired = true;
                return '';
            }
            //* Sinon, retourner les données.
            return $objSession->data;
        }
        //* Si pas encore de session, retourner une chaîne vide.
        return '';
    }

    /**
     * Ercit les données de session.
     *
     * @param string $id SID
     * @param string $data Données de session
     * @return boolean Pour usage interne PHP, ici systématiquement true.
     */
    public function write($id, $data): bool
    {
        if (!self::$expired) {

            //* Tenter une requête INSERT.
            try {
                $q = 'INSERT INTO session VALUES(:sid, :data, :dateSession)';
                $params = [':sid' => $id, ':data' => $data, ':dateSession' => date('Y-m-d H:i:s')];
                DBAL::get()->xeq($q, $params);
            }
            //* Si erreur (doublon de SID), exécuter une requête UPDATE.
            catch (PDOException $e) {
                $q = 'UPDATE session SET data =  :data, :dateSession = :dateSession WHERE sid = :sid';
                $params = [':sid' => $id, ':data' => $data, ':dateSession' => date('Y-m-d H:i:s')];
                DBAL::get()->xeq($q, $params);
            }
        }
        return true;
    }

    /**
     * Inutile ici.
     *
     * @return boolean Pour un usage interne PHP, ici systématiquement true.
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Détruire la session (cookie + DB).
     *
     * @param string $id SID.
     * @return boolean Pour un usage interne PHP, ici systématiquement true.
     */
    public function destroy($id): bool
    {
        //* Récupérer le nom de la session.
        $sessionName = session_name();
        //* Supprimer le cookie du navigateur.
        setcookie($sessionName, '', 1, '/',);
        //* Supprimer la clé du tableau des cookies du serveur.
        unset($_COOKIE[$sessionName]);
        //* Supprimer la session de la DB.
        $q = "DELETE FROM session WHERE sid = :sid";
        $params = [':sid' => $id];
        DBAL::get()->xeq($q, $params);
        //* Retourner systématiquement true        
        return true;
    }

    /**
     * Garbage collector, supprime les sessions expirées en DB.
     * @param int $maxlifetime Durée de vie maxi d'une session (secondes).
     * @return integer|false True si la suppression a réussi, false sinon.
     */
    public function gc($maxlifetime): int|false
    {
        //* Créer la requête.
        $q = "DELETE FROM session WHERE dateSession < :dateMin";
        $params = [':dateMin' => date('Y-m-d H:i:s', time() - $maxlifetime)];
        //* Retourner le nombre d'enregistrements.
        return DBAL::get()->xeq($q, $params)->nb();
    }
}