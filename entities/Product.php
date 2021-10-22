<?php

declare(strict_types=1);


namespace entities;

use entities\Category;
use peps\core\ORMDB;
use peps\core\DBAL;
use peps\core\Validator;

/**
 * Entité Product
 * Toutes les propriétés à null par défaut pour les formulaires de saisie.
 * 
 * @see DBAL
 * @see ORMDB
 */
class Product extends ORMDB implements Validator
{

    //* Messages d'erreur.
    protected const ERR_INVALID_PK = "Clé primaire invalide";
    protected const ERR_INVALID_CATEGORY = "Catégorie invalide";
    protected const ERR_INVALID_NAME = "Nom invalide";
    protected const ERR_INVALID_REF = "Référence invalide";
    protected const ERR_INVALID_ALREADY_EXISTS = "Référence déjà existante";
    protected const ERR_INVALID_PRICE = "Prix invalide";


    /**
     * Id.
     */
    public ?int $idProduct = null;

    /**
     * FK de la categorie.
     */
    public ?int $idCategory = null;

    /** 
     * Nom. 
     */
    public ?string $name = null;

    /** 
     * Réference. 
     */
    public ?string $ref = null;

    /**
     * Prix.
     */
    public ?float $price = null;

    /** 
     * Catégories de produit.  
     */
    protected ?Category $category = null;

    /**
     * Constructeur.
     */
    public function __construct(int $idProduct = null)
    {
        $this->idProduct = $idProduct;
    }

    /**
     * Retourne un tableau des categories triés pas nom de cette catégorie.
     * Lazy loading
     */
    protected function getCategory(): Category
    {
        if (empty($this->category)) {
            //* Solution 1:
            //$this->category = Category::findOneBy(['idCategory'=> $this->idCategory]);

            //* Solution 2:
            $category = new Category($this->idCategory);
            $this->category = $category->hydrate() ? $category : null;
        }
        return $this->category;
    }

    /**
     * {@inheritDoc}
     *
     */
    public function validate(?array &$errors = []): bool
    {
        //* Flag
        $valid = true;

        //* Si présent, vérifier idProduct (PK) et son existance en DB.
        if ($this->idProduct && ($this->idProduct < 1 || !(new Product($this->idProduct))->hydrate())) {
            $valid = false;
            $errors[] = self::ERR_INVALID_PK;
        }

        //* Vérifier idCatégory (PK, obligatoire) et son existance en DB.
        if (!$this->idCategory || ($this->idCategory < 1 || !(new Category($this->idCategory))->hydrate())) {
            $valid = false;
            $errors[] = self::ERR_INVALID_CATEGORY;
        }
        //* Vérifier le nom (obligatoire et max 50 caractères).
        if (!$this->name || mb_strlen($this->name) > 50) {
            $valid = false;
            $errors[] = self::ERR_INVALID_NAME;
        }


        //* Vérifier la référence (obligatoire et max 10 caractères).
        if (!$this->ref || mb_strlen($this->name) > 10) {
            $valid = false;
            $errors[] = self::ERR_INVALID_REF;
        }


        //* Vérifier l'unicité de la référence en DB.
        if ($this->refAlreadyExists()) {
            $valid = false;
            $errors[] = self::ERR_INVALID_ALREADY_EXISTS;
        }

        //* Vérifier le prix (obligatoire et > 0 et < 10000).
        if (!$this->price || $this->price <= 0 || $this->price >= 10000) {
            $valid = false;
            $errors[] = self::ERR_INVALID_PRICE;
        }
        return $valid;
    }

    /**
     * Vérifie si la référence existe déjà en DB ou non (sans tenir compte de $this lui même)
     *
     * @return boolean True ou False selon la référence existe déjà ou non.
     */
    protected function refAlreadyExists(): bool
    {
        //* Rechercher un eventuel doublon.
        $product = self::findOneBy(['ref' => $this->ref]);
        //* Ne pas compter celui qui aurait le même idProduct.
        return (bool) $product && $this->idProduct != $product->idProduct;
    }
}