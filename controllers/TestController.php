<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use peps\core\Router;
use entities\Product;
use entities\User;


/**
 * Classe 100% static.
 * Contrôle les produits
 */
final class TestController
{
    /** 
     * Constructeur privé.
     */
    private function __construct()
    {
    }

    /** 
     * methode de test.
     * 
     * GET /test
     */
    public static function test(): void
    {
        $user = new User();
        $user->log = 'tom';
        $user->pwd = 'tom';
        var_dump($user->login());
        var_dump(User::getUserSession());
    }
}