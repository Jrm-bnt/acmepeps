<?php

declare(strict_types=1);

namespace cfg;

/**
 * Classe 100% statique de configuration de l'application pour le serveur local.
 *
 * @see CfgApp
 */
final class CfgLocal extends CfgApp
{
	/**
	 * Constructeur privé.
	 */
	private function __construct()
	{
	}

	/**
	 * Initialise la configuration.
	 */
	public static function init(): void
	{
		// Inscription des constantes de la classe parente.
		parent::init();

		// Driver PDO de la DB.
		self::register('dbDriver', 'mysql');

		// Hôte de la DB.
		self::register('dbHost', 'localhost');

		// Port de l'hôte.
		self::register('dbPort', 3306);

		// Nom de la DB.
		self::register('dbName', 'acme');

		// Identifiant de l'utilisateur de la DB.
		self::register('dbLog', 'root');

		// Mot de passe de l'utilisateur de la DB.
		self::register('dbPwd', '');

		// Jeu de caractères de la DB.
		self::register('dbCharset', 'utf8mb4');

		// Durée de vie des sessions (secondes).
		self::register('sessionTimeout', 300);

		// Mode des sessions (PERSISTENT | HYBRID | ABSOLUTE).
		self::register('sessionMode', 'TODO');
	}
}
