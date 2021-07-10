<?php

namespace App\Service;

use PDO;
use App\Entity\User;

/**
 * Le paginateur
 */
class Paginator
{
   /**
    * Le Repository de Poste
    * 
    * @var mixed
    */
   private $repo;

   /**
    * La limite
    *
    * @var integer
    */
   private $limit = 8;

   /**
    * La page courante
    *
    * @var int
    */
   private $currentPage;

   /**
    * Le paramètre de recherche
    *
    * @var int
    */
   private $query;

   /**
    * L'abréviation de la table
    *
    * @var string
    */
   private $alias;

   /**
    * La colonne d'une table spécifique
    *
    * @var string
    */
   private $column;

   /**
    * Sert à prendre en charge tous les paramètres, et de retourner l'objet courant
    *
    * @param array $params
    * @return self
    */
   public function setParams(array $params): self
   {
      [$this->repo, $this->currentPage, $this->query, $this->alias, $this->column] = $params;

      return $this;
   }

   /**
    * Sert à calculer et renvoyer l'offset
    *
    * @return integer
    */
   public function getOffset(): int
   {
      return ($this->limit * $this->currentPage) - $this->limit;
   }

   /**
    * Permet de calculer et récupérer le nombre de pages
    *
    * @param User|null $user
    * @return integer
    */
   public function getCountPages(?User $user = null): int
   {
      // Séléctionnement de la table
      $select = $this->repo
                        ->createQueryBuilder($this->alias)
                        ->select("COUNT($this->alias) AS count");

      // Si l'utilisateur ayant fait une recherche
      if ($this->query !== ''):
         $select
            ->where("{$this->alias}.{$this->column} LIKE :query")
            ->setParameter('query', '%'.$this->query.'%', PDO::PARAM_STR);
      endif;

      // En cas de la page de l'utilisateur
      if ($user):
         $select
            ->andWhere("{$this->alias}.author = :user")
            ->setParameter("user", $user);
      endif;

      // La requête
      $query = $select->getQuery();

      // Le nombre de postes
      $count = (int) $query->getResult()[0]['count'];

      return \ceil($count / $this->limit);

   }

   /**
    * Permet d'avoir des postes paginés ou d'une recherche sur un ou plusieurs postes précis
    *
    * @param User|null $user
    * @return array
    */
   public function getData(?User $user = null): array
   {
      $select = $this->repo
                        ->createQueryBuilder($this->alias)
                        ->select($this->alias);

      // Si l'utilisateur ayant décidé de faire une recherche
      if ($this->query !== ''):
         $select
            ->where("{$this->alias}.{$this->column} LIKE :query")
            ->setParameter('query', '%'.$this->query.'%', PDO::PARAM_STR);
      endif;

      // En cas de la page d'utilisateur
      if ($user):
         $select
            ->andWhere($this->alias.".author = :user")
            ->setParameter('user', $user);
      endif;

      $query = $select
                  ->orderBy("{$this->alias}.createdAt", 'DESC')
                  ->groupBy("{$this->alias}.{$this->column}")
                  ->setFirstResult($this->getOffset())
                  ->setMaxResults($this->limit)
                  ->getQuery();
      
      return $query->getResult();
   }

}
