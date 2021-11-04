<?php

declare(strict_types=1);

namespace views\inc;

use entities\User;

?>
<header>

    <div class="user">
        <?php

        if (User::getUserSession()) {

        ?>
        <?= User::getUserSession()->lastName ?> <?= User::getUserSession()->firstName ?>
        &nbsp;&nbsp;&nbsp;
        [ <a href="/user/logout">Déconnexion</a> ]&nbsp;&nbsp;&nbsp;[ <a href="/select">Select</a> ]
        <?php
        } else {
        ?>
        [ <a href="/user/signin">Connexion</a> ]&nbsp;&nbsp;&nbsp;[ <a href="/select">Select</a> ]
        <?php
        }
        ?>
    </div>
</header>