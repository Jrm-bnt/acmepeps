<?php

declare(strict_types=1);

namespace views;

use peps\core\Cfg;

?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Cfg::get('appTitle') ?></title>
    <link rel="stylesheet" href="/assets/css/acme.css">
</head>

<body>
    <header></header>
    <main>
        <?php
        foreach ($categories as $category) {
        ?>
        <div class="category">
            <a href="/product/create/<?= $category->idCategory ?>">
                <img src="/assets/img/ico_create.svg" class="ico">

            </a>
            <?= $category->name ?>
        </div>

        <?php
            foreach ($category->products as $product) {
                //* Ajouter dynamiquement la propriété idImg
                $product->idImg = file_exists("assets/img/product_{$product->idProduct}_small.jpg") ? $product->idProduct : 0;
            ?>

        <div class="blockProduct">
            <a href="/product/show/<?= $product->idProduct ?>">
                <img class="thunbnail" src="/assets/img/product_<?= $product->idImg ?>_small.jpg" alt="chaussure" />
                <div class="name"><?= $product->name ?></div>
            </a>
            <a href="/product/update/<?= $product->idProduct ?>" class="ico update">
                <img src="/assets/img/ico_update.svg"></a>
            <img src="/assets/img/ico_delete.svg" alt="delete" class="ico delete"
                onclick="deleteAll(<?= $product->idProduct ?>)">
            <img src="/assets/img/ico_deleteImg.svg" alt="delete" class="ico deleteImg"
                onclick="deleteImg(<?= $product->idProduct ?>)">
        </div>
        <?php
            }
        }
        ?>
    </main>
    <footer></footer>
    <script src="/assets/js/listProducts.js"></script>
</body>

</html>