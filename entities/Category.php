<?php

declare(strict_types=1);

namespace entities;

use entities\Product;
use JsonSerializable;
use peps\core\ORMDB;
use peps\core\DBAL;
use peps\core\Validator;

/**
 * Entité Category
 * Toutes les propriétés à null par défaut pour les formulaires de saisie.

 * 
 * @see DBAL
 * @see ORMDB
 */
class Category extends ORMDB implements Validator, JsonSerializable
{


    /**
     * PK.
     */
    public ?int $idCategory = null;

    /**
     * Nom.
     */
    public ?string $name = null;

    /** 
     * Collection des produits de la catégorie.
     * @var null | Product[]
     */
    protected ?array $products = null;

    /**
     * Constructeur
     */
    public function __construct(int $idCategory = null)
    {
        $this->idCategory = $idCategory;
    }

    /**
     * Retourne un tableau des categories triés pas nom de cette catégorie.
     * Lazy loading
     */
    protected function getProducts(): array|null
    {
        if (empty($this->products))
            $this->products = Product::findAllBy(['idCategory' => $this->idCategory], ['name' => 'ASC']);
        return $this->products;
    }


    public function validate(?array &$errors = null): bool
    {
        return true;
    }
}
