<?php

declare(strict_types=1);

namespace peps\core;

/**
 * Classe 100% statique d'autoload.
 */
final class autoload
{
    /** 
     * Constructeur privé.
     */
    private function __construct()
    {
    }

    /** 
     * Initialise l'autoload.
     * DOIT être appelée depuis le contrôleur frontal EN TOUT PREMIER. 
     */
    public static function init(): void
    {
        //* Inscrire la fonction d'autoload dans la pile d'autoload.
        spl_autoload_register(fn ($className) => require  $className . '.php');
    }
}