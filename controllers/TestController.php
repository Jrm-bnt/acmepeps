<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use peps\core\Router;
use entities\Product;
use entities\User;
use peps\core\DBAL;

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
    // public static function test(): void
    // {
    //     mail('jrm.devweb@gmail.com', 'test', 'ceci est une test');
    // }
    public static function email()
    {
        mail('test.devweb31@gmail.com', 'Test', 'ceci est une test');
    }

    public static function test(): void
    {
        Router::render('test.php');
    }

    /**
     * Méthode d'auto-completion.
     * 
     * GET /test/autocomplete/{value}
     */
    public static function autocomplete(array $params): void
    {
        // Récupérer value.

        $value = $params['value'];
        //* si non-vide, récupérer les produits correspondant
        if (!empty($value)) {

            // Exécuter la requête.
            $q = "SELECT * FROM product WHERE name LIKE :value ORDER BY name";
            $paramsSQL = [':value' => "%{$value}%"];
            $products = DBAL::get()->xeq($q, $paramsSQL)->findAll(Product::class);
        }
        //* Sinon,retourner un tableau vide
        else $products = [];
        //* envoyer le tableau encodé en JSON
        Router::json(json_encode($products));
    }
}