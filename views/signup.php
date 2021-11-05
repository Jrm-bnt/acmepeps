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
<header></header>

<body>
    <main id="main">
        <div class="category">
            <a href="/">Accueil</a> &gt; Inscription
        </div>
        <div class="error"><?= implode('<br/>', $errors ?? [])  ?></div>
        <form name="form1" action="/user/save" method="POST">
            <div class="item">
                <label>Identifiant</label>
                <input name="log" value="<?= $user->log ?>" size="10" maxlength="10" required="required" />
            </div>
            <div class="item">
                <label>Nom</label>
                <input name="lastName" value="<?= $user->lastName ?>" size="10" maxlength="10" required="required" />
            </div>

            <div class="item">
                <label>Prénom</label>
                <input name="firstName" value="<?= $user->firstName ?>" size="10" maxlength="10" required="required" />
            </div>

            <div class="item">
                <label>Email</label>
                <input type="email" name="email" value="<?= $user->email ?>" size="20" maxlength="25"
                    required="required" />
            </div>

            <div class="item">
                <label>Mot de passe</label>
                <input type="password" name="pwd" size="10" maxlength="10" required="required" />

            </div>
            <div class="item">
                <label> Confirmation mdp</label>
                <input type="password" name="pwd2" size="10" maxlength="10" required="required" oninput="checkPwd()" />

            </div>
            <div class="item">
                <label></label>
                <input type="submit" name="submit" value="inscription" disabled="disabled" />
                <div class="item">
                    <label></label>
                </div>
        </form>

    </main>
    <script src="/assets/js/signup.js"></script>
</body>


</html>