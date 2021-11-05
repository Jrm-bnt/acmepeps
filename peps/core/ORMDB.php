<?php

declare(strict_types=1);

namespace peps\core;

use Error;
use JsonSerializable;
use ReflectionClass;
use ReflectionProperty;

/**
 * Implémentation de la persistance ORM en DB via DBAL.
 * Les classes entités DEVRAIENT étendre cette classe.
 * *********************************************************
 * Règles à respecter pour profiter de cette implémentation.
 * Sinon, redéfinir ses méthodes dans les classes entités.
 * *********************************************************
 * -1- Tables nommées selon cet exemple: classe 'TrucChose', table 'trucChose'.
 * -2- PK auto-incrémentée nommée selon cet exemple: table 'trucChose', PK 'idTrucChose'.
 * -3- Chaque colonne correspond à une propriété PUBLIC du même nom. Les autres propriétés NE sont pas PUBLIC.
 * -4- Si une propriété 'trucChose' est inaccessible, la méthode 'getTrucChose()' sera invoquée si elle existe. Sinon, null sera retourné.
 */
class ORMDB implements ORM, JsonSerializable
{
	/**
	 * Hydrate l'entité depuis le système de stockage.
	 *
	 * @return boolean True ou false selon que l'hydratation a réussi ou non.
	 */
	function hydrate(): bool
	{
		// Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
		$className = (new ReflectionClass($this))->getShortName();
		$tableName = lcfirst($className);
		// Construire le nom de la PK à partir du nom de la classe.
		$pkName = "id{$className}";
		$q = "SELECT * FROM {$tableName} WHERE {$pkName} = :__ID__";
		$params = [':__ID__' => $this->$pkName];
		// Exécuter la requête et hydrater $this.
		return DBAL::get()->xeq($q, $params)->into($this);
	}

	/**
	 * Persiste l'entité vers le système de stockage.
	 *
	 * @return boolean True systématiquement.
	 */
	function persist(): bool
	{
		// Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
		$rc = new ReflectionClass($this);
		$className = $rc->getShortName();
		$tableName = lcfirst($className);
		// Construire le nom de la PK à partir du nom de la classe.
		$pkName = "id{$className}";
		// Récupérer le tableau des propriétés publiques de la classe.
		$properties = $rc->getProperties(ReflectionProperty::IS_PUBLIC);
		// Initialiser les éléments de requêtes et le tableau des paramètres.
		$strInsertColums = $strInsertValues = $strUpdate = '';
		$params = [];
		// Pour chaque propriété, construire les éléments des requêtes SQL et compléter les paramètres.
		foreach ($properties as $property) {
			$propertyName = $property->getName();
			$strInsertColums .= "{$propertyName},";
			$strInsertValues .= ":{$propertyName},";
			$strUpdate .= "{$propertyName} = :{$propertyName},";
			$params[":{$propertyName}"] = $this->$propertyName;
		}
		// Supprimer la dernière virgule de chaque élément de requête.
		$strInsertColums = rtrim($strInsertColums, ',');
		$strInsertValues = rtrim($strInsertValues, ',');
		$strUpdate = rtrim($strUpdate, ',');
		// Créer les requêtes et les paramètres SQL.
		$strInsert = "INSERT INTO {$tableName} ({$strInsertColums}) VALUES({$strInsertValues})";
		$strUpdate = "UPDATE {$tableName} SET {$strUpdate} WHERE {$pkName} = :__ID__";
		// Finir de compléter les tableaux de paramètres.
		$paramsInsert = $paramsUpdate = $params;
		$paramsUpdate[':__ID__'] = $this->$pkName;
		// Exécuter la requête INSERT ou UPDATE et, si INSERT, récupérer la PK auto-incrémentée.
		$dbal = DBAL::get();
		$this->$pkName ? $dbal->xeq($strUpdate, $paramsUpdate) : $this->$pkName = $dbal->xeq($strInsert, $paramsInsert)->pk();
		// Retourner true systématiquement.
		return true;
	}

	/**
	 * Supprime l'entité du système de stockage.
	 *
	 * @return boolean True ou false selon que la suppression a réussi ou non.
	 */
	function remove(): bool
	{
		// Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
		$rc = new ReflectionClass($this);
		$className = $rc->getShortName();
		$tableName = lcfirst($className);
		// Construire le nom de la PK à partir du nom de la classe.
		$pkName = "id{$className}";
		// Si PK non renseignée, retourner false.
		if (!$this->$pkName)
			return false;
		// Exécuter la requête et retourner la conclusion.
		$q = "DELETE FROM {$tableName} WHERE {$pkName} = :__ID__";
		$params = [':__ID__' => $this->$pkName];
		return (bool) DBAL::get()->xeq($q, $params)->nb();
	}

	/**
	 * Sélectionne des entités correspondant aux critères dans le système de stockage.
	 * Retourne un tableau d'instances (implémentant ORM).
	 *
	 * @param array $filters Tableau associatif de filtres d'égalité reliées par 'AND' sous la forme 'champ' = 'valeur'. Ex: ['name' => 'truc', 'idCategory' => 3].
	 * @param array $sortKeys Tableau associatif de clés de tri sous la forme 'champ' => 'ASC' | 'DESC'. Ex: ['name' => 'DESC', 'price' => 'ASC'].
	 * @param string $limit Limite de la sélection.
	 *                      Ex: '3' signifie 3 entités à partir de la première.
	 *                      Ex: '2,5' signifie 5 entités à partir de la troisième incluse.
	 * @return array Tableau d'instances.
	 */
	public static function findAllBy(array $filters = [], array $sortKeys = [], string $limit = ''): array
	{
		// Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
		$className = (new ReflectionClass(static::class))->getShortName();
		$tableName = lcfirst($className);
		// Initialiser les requêtes SQL et les paramètres SQL.
		$q = "SELECT * FROM {$tableName}";
		$params = [];
		if ($filters) {
			// Si filtres, construire la clause WHERE.
			$q .= " WHERE";
			foreach ($filters as $col => $val) {
				$q .= " {$col} = :{$col} AND";
				$params[":{$col}"] = $val;
			}
			// Supprimer le dernier ' AND'.
			$q = rtrim($q, ' AND');
		}
		if ($sortKeys) {
			// Si clés de tri, construire la clause ORDER BY.
			$q .= " ORDER BY";
			foreach ($sortKeys as $col => $sortOrder)
				$q .= " {$col} {$sortOrder},";
			// Supprimer la dernière virgule.
			$q = rtrim($q, ',');
		}
		// Si limite, ajouter la clause LIMIT.
		if ($limit)
			$q .= " LIMIT {$limit}";
		// Exécuter la requête et retourner le tableau.
		return DBAL::get()->xeq($q, $params)->findAll(static::class);
	}

	/**
	 * Sélectionne une entité correspondant aux critères dans le système de stockage.
	 * Retourne une instance (implémentant ORM) ou null si aucune correspondance.
	 *
	 * @param array $filters Tableau associatif de filtres d'égalité reliées par 'AND' sous la forme 'champ' = 'valeur'. Ex: ['name' => 'truc', 'idCategory' => 3].
	 * @return ORM|null L'instance ou null.
	 */
	static function findOneBy(array $filters = []): ?ORM
	{
		return self::findAllBy($filters, [], '1')[0] ?? null;
	}

	/**
	 * Retourne le résultat de l'invocation de la méthode get{PropertyName}() si elle existe.
	 * Sinon retourne null.
	 *
	 * @param string $propertyName Nom de la propriété.
	 * @return mixed Dépend de la classe enfant et de la propriété.
	 */
	public function __get(string $propertyName): mixed
	{
		// Construire le nom de la méthode à invoquer.
		$methodName = 'get' . ucfirst($propertyName);
		// Tenter de l'invoquer.
		try {
			return $this->$methodName();
		} catch (Error $e) {
			return null;
		}
	}

	/**
	 * Appelé automatiquement par json_encode().
	 * Spécifie les propriétés (même protected) qui seront sérialisées en JSON.
	 */
	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
}