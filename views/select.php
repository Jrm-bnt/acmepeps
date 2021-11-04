<?php

declare(strict_types=1);

namespace views;

use controllers\TestController;
use peps\core\Cfg;

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Cfg::get('appTitle') ?></title>
    <link rel="stylesheet" href="/assets/css/acme.css" />
    <style>
    #selectProducts {
        visibility: hidden;
    }
    </style>
</head>

<body>
    <?php require 'views/inc/header.php' ?>
    <main>
        <div class="category">
            <a href="/">Accueil</a> &gt; Select

        </div>
        <form>
            <div class="item">
                <label></label>

                <select name="idCategory" id="idCategory">
                    <option value="0">Choissisez une categorie</option>
                    <?php
                    foreach ($categories as $category) {
                    ?>


                    <option value="<?= $category->idCategory ?>"><?= $category->name ?></option>
                    <?php
                    }

                    ?>


                </select>
            </div>
            <div class="item" id="selectProducts">
                <label></label>
                <select name="idProduct" id="idProduct">
                    <option value="0">Choissisez un produit</option>


                </select>
            </div>

        </form>
    </main>
    <script>
    let categories = <?= json_encode($categories) ?>;
    </script>
    <script src="/assets/js/select.js"></script>
</body>

</html>