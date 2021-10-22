<?php

declare(strict_types=1);

namespace controllers;

use entities\Category;
use peps\core\Router;
use entities\Product;


/**
 * Classe 100% static.
 * Contrôle les produits
 */
final class ProductController
{
    /** 
     * Constructeur privé.
     */
    private function __construct()
    {
    }

    /** 
     * Affiche les produits par catégorie.
     * 
     * GET /
     * GET /product/list
     */
    public static function list(): void
    {
        //* Récupere toutes les catégories.
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        //* Rendre la vue
        Router::render('listProducts.php', ['categories' => $categories]);
        exit('ProductController.list');
    }

    /**  
     * Affiche le détail d'un produit.
     *
     *GET /product/show/{idProduit}
     */
    public static function show(array $params): void
    {
        //* Récuperer idProduct.
        $idProduct = (int)$params['idProduct'];

        //* Créer le produit et l'hydrater
        $product = new Product($idProduct);

        //* Hydrater le produit et si idProduct inexistant, rendre la vue noProduct.
        if (!$product->hydrate())
            Router::render('noProduct.php'); //* Il y a un exit dans render qui evite de mettre un Else
        //* Ajouter dynamiquement la propriété idImg.
        $product->idImg = file_exists("assets/img/product_{$product->idProduct}_big.jpg") ? $product->idProduct : 0;
        //* Rendre la vue showProduct
        Router::render('showProduct.php', ['product' => $product]);
    }
    /**
     * Supprime un produit et/ou ses images.
     *
     * GET/product/delete/{idProduct}/{mode=all | img}
     * 
     * @param array $params Tableau associatif des paramètres.
     * @return void
     */
    public static function delete(array $params): void
    {
        //*récuperer les params idPorduct en int et mode.
        $idProduct = (int) $params["idProduct"];
        $mode = $params["mode"];

        //* Si mode 'all', créer un produit pour le supprimer.
        if ($mode === "all") (new Product($idProduct))->remove();

        //* Dans tous les cas tenter de supprimer images.
        @unlink("assets/img/product_{$idProduct}_small.jpg");
        @unlink("assets/img/product_{$idProduct}_big.jpg");

        //* Rediriger vers la list des produits. (synchrone) voir listProducts.js.
        // Router::redirect('/product/list');

        //* Faire un echo sans précision (asynchrone)
        Router::json(json_encode(''));
    }

    /**
     * Affiche le formulaire d'un produit.
     *  
     * GET /product/create/{idCategory}
     *
     * @param array $params Tableau associatif des paramètres.
     * @return void
     */
    public static function create(array $params): void
    {
        //*Récuperer idCategory.
        $idCategory = (int)$params['idCategory'];

        //* Créer un Produit. (attendu par la vue)
        $product = new Product();
        //* Renseigner idCategory du produit pour caler le menu déroualnt des catégories.
        $product->idCategory = $idCategory;

        //* Récuperer toute les categories. $categories
        $categories = Category::findAllBy([], ['name' => 'ASC']);

        //* Rendre la vue.
        Router::render('editProduct.php', ['product' => $product, 'categories' => $categories]);
    }


    /**
     * Affiche le formulaire de modification d'un produit
     * 
     * GET /product/update/{idProduct}
     *
     * @param array $params
     * @return void
     */
    public static function update(array $params): void
    {
        //*Récuperer idProduct.
        $idProduct = (int)$params['idProduct'];

        //* Créer le Produit correspondant.
        $product = new Product($idProduct);

        //* Hydrater le produit
        $product->hydrate() ?: Router::render('noProduct.php');

        //* Récuperer toute les categories.
        $categories = Category::findAllBy([], ['name' => 'ASC']);

        //* Rendre la vue.
        Router::render('editProduct.php', ['product' => $product, 'categories' => $categories]);
    }


    /**
     * Persiste le produit en ajout ou modification.
     * 
     *  POST/prodcut/save
     */
    public static function save(): void
    {
        //* Initialiser le tableau des erreurs
        $errors = [];

        //* Créer le produit.
        $product = new Product();

        //* Récupérer et filtrer les données.
        $product->idProduct = filter_input(INPUT_POST, 'idProduct', FILTER_VALIDATE_INT) ?: null;
        $product->idCategory = filter_input(INPUT_POST, 'idCategory', FILTER_VALIDATE_INT) ?: null;
        $product->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES) ?: null;
        $product->ref = filter_input(INPUT_POST, 'ref', FILTER_SANITIZE_STRING) ?: null;
        $product->price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT) ?: null;

        //* Si données valides, persister le produit et rediriger vers la liste.
        if ($product->validate($errors)) {
            $product->persist();
            Router::redirect('/');
        }

        //* Sinon, retrouver toutes les catégories et rendre la vue editProduct.
        $categories = Category::findAllBy([], ['name' => 'ASC']);
        Router::render('editProduct.php', ['product' => $product, 'categories' => $categories, 'errors' => $errors]);
    }
}