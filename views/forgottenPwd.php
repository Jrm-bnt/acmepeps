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
            <a href="/">Accueil</a> &gt; Mot de passe oublié.
        </div>
        <div class="error"><?= implode('<br/>', $errors ?? [])  ?></div>
        <form name="form1" action="/user/newPwd" method="POST">
            <div class="item">
                <label>Identifiant</label>
                <input name="log" value="<?= $log ?>" size="30" maxlength="10" required="required" />
            </div>
            <div class="item">
                <label></label>
                <input type="submit" value="Envoyer" />
            </div>
        </form>
    </main>
</body>
<footer></footer>

</html>