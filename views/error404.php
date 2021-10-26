<?php

declare(strict_types=1);

namespace views;

use peps\core\Cfg;

?>
<!DOCTYPE html>
<html lang="fr">
<?php require 'views/inc/header.php' ?>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= Cfg::get('appTitle') ?></title>
    <link rel="stylesheet" href="/assets/css/acme.css">
</head>
<header></header>

<body>
    <main>
        <div class="category">
            <a href="/">Accueil</a> &gt; Oups !
        </div>
        <img src="/assets/img/error404.png" alt="Oups !">
    </main>
</body>
<footer></footer>

</html>