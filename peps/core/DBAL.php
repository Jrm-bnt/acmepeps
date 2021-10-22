<?php

declare(strict_types=1);

namespace peps\core;

use PDO;
use PDOStatement;
use stdClass;

/**
 * DBAL via PDO. 
 * Design pattern Singleton.
 */
final class DBAL
{
   /** 
    * option de connexion commune à toutes les DB :
    *     - Gestion des erreurs basée sur des exeptions.
    *     - Type dse colonnes respecté.
    *     - Requêtes réellement préparées plutôt que simplement simulées.
    */
   private const OPTIONS = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_STRINGIFY_FETCHES => false,
      PDO::ATTR_EMULATE_PREPARES => false
   ];


   /**
    * Instance Singleton.
    */
   private static ?self $instance = null;

   /**
    * Instance de PDO.
    */
   private ?PDO $db = null;

   /**
    * Instance de PDOStatement.
    */
   private ?PDOStatement $stmt = null;

   /**
    * Mode d'enregistrement retrouvés (SELECT) ou affectés par la dernière requête.
    */
   private ?int $nb = null;

   /**
    * Consructeur privé.
    */
   private function __construct()
   {
   }

   /**
    * Créer l'instance singleton et l'instance PDO encapsulée. 
    *
    * @param string $driver Driver DB.
    * @param string $host Hôte DB.
    * @param integer $port Port de l'hôte DB.
    * @param string $dbName Nom de la base de données.
    * @param string $log Identifiant de l'utilisateur DB.
    * @param string $pwd Mot de passe de l'utilisateur DB.
    * @param string $charset Jeu de charactères.
    */
   public static function init(
      string $driver,
      string $host,
      int $port,
      string $dbName,
      string $log,
      string $pwd,
      string $charset,
   ): void {
      //* Si déjà initalisée, ne rien faire.
      if (self::$instance)
         return;
      //* Créer la chaîne DSN.
      $dsn = "{$driver}:host={$host};port={$port};dbname={$dbName}; charset={$charset}";
      //* Créer l'instance Singleton.
      self::$instance = new self();
      //* Créer l'instance PDO.
      self::$instance->db = new PDO($dsn, $log, $pwd, self::OPTIONS);
   }

   /**
    * Retourne l'instance Singleton.
    * La méthode init() devrait avoir été appelée au préalable.
    * 
    * @return self|null Instance de singleton ou null si init() pas encore appelée.
    */
   public static function get(): ?self
   {
      return self::$instance;
   }

   /**
    * Exécute une requête SQL.
    *
    * @param string $q Requête SQL
    * @param array|null $params Tableau associatifs des paramètres (optionnel).
    * @return static $this pour chaînage.
    */
   public function xeq(string $q, ?array $params = null): static
   {
      //* Paramètre présents, préparer et executer la requête.
      if ($params) {
         $this->stmt = $this->db->prepare($q);
         $this->stmt->execute($params);
         //* Récuperer le nombre d'enregistrements retrouvés ou affectés.
         $this->nb = $this->stmt->rowCount();
      } elseif (mb_stripos(ltrim($q), 'SELECT') === 0) {
         //* Si requête SELECT, l'executer avec query().
         $this->stmt = $this->db->query($q);
         //* Récuperer le nombre d'enregistrements retrouvés ou affectés.
         $this->nb = $this->stmt->rowCount();
      } else {
         //* Si requête non SELECT, l'executeur avec exec() récupérer le nombre d'enregistrements affectés.
         $this->nb = $this->db->exec($q);
      }
      return $this;
   }

   /**
    * Retourne le nombre d'enregistrement retrouvés (SELECT) ou affectés par la dernière requête executée.
    * 
    * @return interger Le nombre d'enregistrement
    */
   public function nb(): int
   {
      return $this->nb;
   }

   /**
    * Retourne un tableau d'instance d'une classe donnée en exploitant le derniere jeu d'enregistrement.
    * Une requête SELECT devrait avoir été exécutée préalablement.
    *
    * @param string $className La classe donnée.
    * @return array Tableau d'instance de la classe donnée.
    */
   public function findAll(string $className = 'stdClass'): array
   {
      //* Si pas de recordset, retourner un tableau vide.
      if (!$this->stmt)
         return [];

      //* Sinon, exploiter le recordset et retourner un tableau d'instances.
      else $this->stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $className);
      return $this->stmt->fetchAll();
   }

   /**
    * Retourne un tableau d'instance d'une classe donnée en exploitant le premier des enregistrements du dernier jeu.
    * Une requête SELECT (typiquement retrouvant au maximum un enregistrement) devrait avoir été exécutée préalablement.
    * Retourne null si aucun recordet ou recordset vide.
    *
    * @param string $className La classe donnée.
    * @return array Tableau d'instance de la classe donnée.
    */

   public function findOne(string $className = 'stdClass'): ?object
   {
      //* Si pas de recordset, retourner un tableau vide.
      if (!$this->stmt)
         return [];

      //* Sinon, exploiter le recordset et retourner la premiere instance instances ou null.
      else $this->stmt->setFetchMode(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $className);
      return $this->stmt->fetch() ?: null;
   }
   /**
    * Hydrate une instance donnée en exploitant le premier enregistrement du dernier jeu.
    * Une requête SELECT (typiquement retrouvant au maximum un enregistrement) devrait avoir été exécutée préalablement.
    *
    * @param object $obj Instance donnée à hydrater.
    * @return boolean True ou false selon que l'hydratation a réussi ou pas.
    */
   public function into(object $obj): bool
   {
      //* Si pas de recordset, retourner un tableau vide.
      if (!$this->stmt)
         return false;

      //* Sinon, exploiter le recordset et hydrater l'instance.
      else $this->stmt->setFetchMode(PDO::FETCH_INTO, $obj);
      return (bool) $this->stmt->fetch();
   }

   /**
    * Retourne la dernière PK  auto-incrémentée.
    * 
    * @return interger PK
    */
   public function pk(): int
   {
      return (int) $this->db->lastInsertId();
   }
   /**
    * Démarre une transaction.
    *
    * @return static $this pour chaînage.
    */
   public function start(): static
   {
      $this->db->beginTransaction();
      return $this;
   }

   /**
    * Définit un point de restauration dans la transaction en cours.
    *
    * @param string $label Nom du point de restauration
    * @return static $this pour chaônage
    */
   public function savepoint(string $label): static
   {

      $q = "SAVEPOINT {$label}";
      return $this->xeq($q);
   }

   /**
    * Effectue un roolback au point de restauration donné ou au départ si absent.
    *
    * @param string|null $label Nom du point de restauration (optionnel)
    * @return static $this pour chaînage
    */
   public function roolback(?string $label = null): static
   {
      $q = "ROLLBACK";
      if ($label)
         $q .= "TO {$label}";
      else return $this->xeq($q);
   }
   /**
    * Valide la transaction en cours.
    *
    * @return static $this pour chaînage.
    */
   public function commit(): static
   {
      $this->db->commit();
      return $this;
   }
}