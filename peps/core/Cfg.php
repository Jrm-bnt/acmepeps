<?php

declare(strict_types=1);

namespace peps\core;

use Locale;
use NumberFormatter;

/**
 * Classe 100% statique de configuration initiale de l'application.
 * Elle doit etre étendu dans l'application par une classe de configuration générale elle-même étednue par une classe finale par serveur.
 * Extention PHP 'intl' soit requise.
 */
class Cfg
{
    /** 
     * Tableau associatif des constantes de configuration. 
     * 
     * @var mixed[]
     */
    private static array $constants = [];

    /** 
     * Constructeur privé.
     */
    private function __construct()
    {
    }

    /** 
     * Inscrit les contantes de base.
     * Doit être redéfinie dans la classe enfant pour y inscire les constantes de l'application en invoquant parent::init() en premiere instruction.
     * Cette méthode doit rester "protected" sauf au dernier niveau d'héritage dans lequel elle DOIT être "public" pour être invoquée depuis le contrôleur frontal.
     * Les clés (en <SNAKE_CASE) enregistrées ici sont LES SEULES accessibles aux classes PEPS.
     * Les clés ajoutées par l'application DEVEAIENT être en camelCase.
     */
    protected static function init(): void
    {
        //* Chemin du fichier JSON des routes depuis la racine de l'application.
        self::register('ROUTE_FILE', 'cfg' . DIRECTORY_SEPARATOR . 'routes.json');

        //* Namespace des contrôleurs.
        self::register('CONTROLLERS_NAMESPACE', 'controllers');

        //* Chemin du repertoire des vues depuis la racine de l'application .
        self::register('VIEWS_DIR', 'views');

        //* Nom de la vue affichant l'erreur 404.
        self::register('ERROR_404_VIEW', 'error404.php');

        //* Locale par défaut en cas de non détection (ex 'fr' ou 'fr-FR').
        self::register('LOCALE_DEFAULT', 'fr');

        //* Locale du client.
        self::register('LOCALE', (function () {
            //* Récuperer les locales du client.
            $locales = filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE', FILTER_SANITIZE_STRING);
            return Locale::acceptFromHttp($locales) ?: self::$constants['LOCALE_DEFAULT'];
        })());

        //* Instance de NumberFormatter pour formater un nombre avec 2 décimales selon la locale.
        self::register('NF_LOCALE_2DEC', (fn () => NumberFormatter::create(self::$constants['LOCALE'], NumberFormatter::PATTERN_DECIMAL, '#,##0.00'))());

        //* Instance de NumberFormatter pour formater un nombre avec 2 décimales selon la norme US (sans séparateur de milliers), typique pour les champs INPUT de type "number" de certains navigateurs.
        self::register('NF_INPUT_2DEC', (fn () => NumberFormatter::create('en-US', NumberFormatter::PATTERN_DECIMAL, '0.00'))());
    }

    /** 
     * Inscrit uen constante dans le tableau des constantes.
     */
    protected final static function register(string $key, mixed $val = null): void
    {
        self::$constants[$key] = $val;
    }

    /**
     * Retourne la valeur de la constante à partir de sa clé.
     * Retourne null si clé inexistante.
     */
    public static final function get(string $key): mixed
    {
        return self::$constants[$key] ?? null; //* null coalissing
    }
}