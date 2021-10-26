<?php

declare(strict_types=1);

namespace peps\core;

use Error;
use ReflectionClass;
use ReflectionProperty;


/**
 * Implémentation de la persistance ORM en DB via DBAL.
 * Les classes entités DEVRAIENT étendre cette classe.
 * 
 * *****************************************************
 * Règles à respecter pour  profiter de cette implémentation.
 * Sinon, redéfinir ses méthode dans les classes entités. 
 * *****************************************************
 * -1- Table nommés selon cet exemple: classe 'TrucChose', table 'trucChose'. 
 * -2- PK auto-incrémentée nommée selon cette exemple : table 'trucChose', PK 'idTrucChose'.
 * -3- Chaque colonne correspond à une propriété PUBLIC de même nom. Les autres propriétés ne sont pas PUBLIC.
 * -4- Si une prpriété 'trucChose' est inaccessible, la méthode 'getTrucChose()' sera invoquée si elle existe. Sinon, null sera retourné.
 */
class ORMDB  implements ORM
{


    /**
     * Hydrate l'entité depuis le système de stockage.
     * 
     * @return boolean True ou False selon que l'hydratation a réussi ou non.
     */
    public function hydrate(): bool
    {
        //*Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
        $className = (new ReflectionClass($this))->getShortName();
        $tableName = lcfirst($className);
        //* Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        //* Récupérer le nom de la classe de l'entité $this pour en déduire le nom de la table.
        $q = "SELECT * FROM {$tableName} WHERE {$pkName}= :__ID__";
        $params = [':__ID__' => $this->$pkName];
        //* Executer la requête et hydrater $this.
        return DBAL::get()->xeq($q, $params)->into($this);
    }


    /**
     * Persist l'entité vers le système de stockage.
     * 
     * @return boolean True systématiquement.
     */
    public function persist(): bool
    {
        //* Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
        $rc = new ReflectionClass($this);
        $className = $rc->getShortName();
        $tableName = lcfirst($className);
        //* Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        //* Récupérer le tableau des propriétés publique de la classe.
        $properties = $rc->getProperties(ReflectionProperty::IS_PUBLIC);
        //* Initialiser les chaînes SQL et les paramètres SQL.
        $strUpdate = "UPDATE {$tableName} SET ";
        $strInsert = "INSERT INTO {$tableName} VALUES(";
        $params = [];
        //* Pour chaque propriété, construire la suite des des requêtes SQL et compléter les paramètres.
        foreach ($properties as $property) {

            $propertyName = $property->getName();
            $strUpdate .= "{$propertyName} = :{$propertyName},";
            $strInsert .= ":{$propertyName},";
            $params[":{$propertyName}"] = $this->$propertyName;
        }

        //* Supprimer la dernière virgule de chaque requête.
        $strInsert = rtrim($strInsert, ',');
        $strUpdate = rtrim($strUpdate, ',');
        //* Finir de compléter les requêtes.
        $strInsert .= ')';
        $strUpdate .= " WHERE {$pkName} = :__ID__";

        //* Finir de compléter les tableaux de paramètres.
        $paramsInsert = $paramsUpdate = $params;
        $paramsUpdate[':__ID__'] = $this->$pkName;

        //* Exécuter la requête INSERT ou UPDATE et, si INSERT, récupérer la PK auto-incrémentée.
        $dbal = DBAL::get();
        $this->$pkName ? $dbal->xeq($strUpdate, $paramsUpdate) : $this->pkName = $dbal->xeq($strInsert, $paramsInsert)->pk();

        //* Retourner True systématiquement.
        return true;
    }



    /**
     * Supprime l'entité du système de stockage de function
     *
     * @return boolean  True ou False selon que la suppression a réussi ou non.
     */
    function remove(): bool
    {
        //*Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
        $className = (new ReflectionClass($this))->getShortName();
        $tableName = lcfirst($className);
        //* Construire le nom de la PK à partir du nom de la classe.
        $pkName = "id{$className}";
        if (!$this->$pkName)
            return false;
        //* Executer la requête et retourner la conclusion.
        $q = "DELETE FROM {$tableName} WHERE {$pkName} = :__ID__";
        $params = [':__ID__' => $this->$pkName];
        //* Executer la requête et hydrater $this.
        return (bool) DBAL::get()->xeq($q, $params)->nb();
    }


    /**
     * Sélectionne des entités correspondant aux critères dans le système de stockage.
     * 
     * @param array $filters Tableau associatif de filtre d'égalité reliée par 'AND' sous la forme 'champ' => 'valeur'. Exemple: ['name'=>'truc', 'idCategory'=>3]  
     * @param array $sortKeys Tableau associatif de clés sous la forme 'champ' => 'ASC'| 'DESC'. Ex: ['name'=>'DESC', 'price'=> 'ASC']
     * @param string $limit Limite de la sélection. 
     *                          Ex '3' signifie 3 entités à partir de la première
     *                          Ex '2,5' signifie 5 entités à partir de la 3ème incluse
     * @return array Tableau d'instances (implémentant) ORM.
     */
    static function findAllBy(array $filters = [], array $sortKeys = [], string $limit = ''): array
    {
        //*Récupérer le nom court (pas pleinement qualifié) de la classe de l'entité $this pour en déduire le nom de la table.
        $className = (new ReflectionClass(static::class))->getShortName();
        $tableName = lcfirst($className);

        //* Initialiser les requêtes SQL et les paramètres SQL.
        $q = "SELECT * FROM {$tableName}";
        $params = [];

        //* Si filtre, construire la clause WHERE.
        if ($filters) {
            $q .= " WHERE ";
            foreach ($filters as $col => $val) {
                $q .= " {$col}= :{$col} AND";
                $params[":{$col}"] = $val;
            }
            //* Supprimer le dernier 'AND'.
            $q = rtrim($q, ' AND');
        }
        if ($sortKeys) {
            //* Si clés de tri, construire la clause ORDER BY.
            $q .= " ORDER BY";
            foreach ($sortKeys as $col => $sortOrder) {
                $q .= " {$col} {$sortOrder},";
            }
            //*supprimer la dernière virgule.
            $q = rtrim($q, ',');
        }
        //* Si limite, ajouter la clause LIMIT.
        if ($limit)
            $q .= " LIMIT {$limit}";

        //* Executer la requête et retourner le tableau.
        return DBAL::get()->xeq($q, $params)->findAll(static::class);
    }

    /**
     * Sélectionne une entité correspondant aux critères dans le système de stockage.
     * Retourne une instance (implémentant ORM) ou null si aucune correspondance.
     * 
     * @param array $filters Tableau associatif de filtre d'égalité reliée par 'AND' sous la forme 'champ' => 'valeur'. Exemple: ['name'=>'truc', 'idCategory'=>3]  
     * @return ORM|null   L'instance ou null.
     */
    public static function findOneBy(array $filters = []): ?ORM
    {
        return self::findAllBy($filters, [], '1')[0] ?? null;
    }

    /**
     * Tente d'invoquer une méthode get{PropertyName} si elle existe.
     * sinon retourne null
     *
     * @param string $propertyName Nom de la propriété. 
     * @return mixed Dépend de la classe enfant et de la propriété.
     */
    public function __get(string $propertyName): mixed
    {

        //* construire le nom de la méthode à invoquer.
        $methodName = 'get' . ucfirst($propertyName);
        //* Tenter de l'invoquer.
        try {
            return $this->$methodName();
        } catch (Error $e) {
            return null;
        }
    }
}