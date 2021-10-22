<?php

declare(strict_types=1);

namespace peps\core;



/**
 * Interface de validation des entités.
 * DEVRAIT être implémentée par les classes entité pour valider els données qu'elles contiennent typiqument avant persistance.
 */
interface Validator
{
    /**
     * Vérifie si l'entité contient des données valides (typiquement avant persistance).
     * 
     * @var string[] $errors Tableau des messages d'erreur passé par référence.
     * 
     * @return boolean True ou False selon que les données sont valide ou non.
     */
    function validate(?array &$errors = []): bool;
}