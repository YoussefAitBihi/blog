<?php

namespace App\Service;

use Exception;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Dashboard
 * 
 * Sert à Créer Le Dashboard
 */
class Dashboard 
{
   /**
    * ObjectManager
    *
    * @var ObjectManager
    */
   private $manager;

   public function __construct(ObjectManager $manager)
   {
      $this->manager = $manager;
   }

   /**
    * Sert à récupérer la totalité de toutes les entités
    *
    * @return array
    */
   public function getCounts(): array
   {  
      // La totalité d'utilisateurs
      $users    = $this->getCountUsers();
      // La totalité des postes
      $posts    = $this->getCountPosts();
      // La totalité des commentaires
      $comments = $this->getCountComments();

      return compact("users", "posts", "comments");
   }

   /**
    * Sert à récupéer
    *
    * @param string $order
    * @param integer $max
    * @return array
    */
   public function getBestOrWorst(string $order, int $max = 5): array
   {
      // Si le futur développeur décidant de dépasser 2 arguments ou de ne pas passer aucun argument
      if (func_num_args() < 1 || func_num_args() > 2):
         throw new Exception("Il faut juste que vous entriez Un ou deux arguments");
      endif;

      /** @var User[] $users contenant les meilleurs ou mauvais utilisateurs */
      $users      = $this->getBestOrWorstUsers($max, $order);
      /** @var Post[] $posts contenant les meilleurs ou mauvais postes  */
      $posts      = $this->getBestOrWorstPosts($max, $order);
      /** @var Comment[] $comments contenant les meilleurs ou mauvais commentaires */
      $comments   = $this->getBestOrWorstComments($order, $max);

      if (is_array($users) && is_array($posts) && is_array($comments)):
         return compact("users", "posts", "comments");
      endif;

      return null;
   }

   /**
    * Sert à récupérer les derniers postes et les derniers utilisateurs inscrit
    *
    * @param integer $max
    * @return array|null
    */
   public function getLast(int $max = 5): ?array
   {
      if (func_num_args() > 1):
         throw new Exception("Cette méthode accepte juste un argument");
      endif;

      /** @var User[] $users contenant les derniers utilisateurs */
      $users = $this->getLastUsers($max);
      /** @var Post[] $posts contenant les derniers postes */
      $posts = $this->getLastPosts($max);
      /** @var Comment[] $comment contenant les derniers commentaires */
      $comments = $this->getLastComments($max);

      if (is_array($users) && is_array($posts) && is_array($comments)):
         return compact("users", "posts", "comments");
      endif;

      return null;
   }

   /**
    * Permet de récupérer la totalité d'utilisateurs
    *
    * @return integer
    */
   private function getCountUsers(): int
   {
      return (int) $this->manager
                           ->createQuery("SELECT COUNT(u) FROM App\Entity\User u")
                           ->getSingleScalarResult();
   }

   /**
    * Permet de récupérer la totalité de postes
    *
    * @return integer
    */
   private function getCountPosts(): int
   {
      return 
         (int) $this->manager
                           ->createQuery("SELECT COUNT(p) FROM App\Entity\Post p")
                           ->getSingleScalarResult();
   }

   /**
    * Permet de récupérer la totalité de commentaires
    *
    * @return integer
    */
   private function getCountComments(): int
   {
      return 
         (int) $this->manager
                           ->createQuery("SELECT COUNT(c) FROM App\Entity\Comment c")
                           ->getSingleScalarResult();
   }

   /**
    * Permet de récupérer les meilleurs et les mauvais utilisateurs
    *
    * @param integer $max
    * @param string $order
    * @return array
    */
   private function getBestOrWorstUsers(int $max = 5, string $order): array
   {
      return 
         $this->manager->createQuery(
                           "SELECT 
                              u, AVG(c.rating) rating 
                           FROM 
                              App\Entity\User u 
                           JOIN 
                              u.posts p 
                           JOIN 
                              p.comments c 
                           GROUP BY 
                              u 
                           ORDER BY 
                              rating $order")
            ->setMaxResults($max)
            ->getResult();
   }

   /**
    * Permet de récupérer les meilleurs et les mauvais postes
    *
    * @param integer $max
    * @param string $order
    * @return array
    */
   private function getBestOrWorstPosts(int $max = 5, string $order): array
   {
      return 
         $this->manager->createQuery(
                           "SELECT 
                              p, AVG(c.rating) rating
                           FROM 
                              App\Entity\Post p 
                           JOIN 
                              p.comments c
                           GROUP BY 
                              p 
                           ORDER BY 
                              rating $order")
            ->setMaxResults($max)
            ->getResult();
   }

   /**
    * Permet de récupérer les meilleurs et les mauvais commentaires
    *
    * @param string $order contenant l'ordre de fetching
    * @param integer $max contenant la limitation de données
    * @return array
    */
   private function getBestOrWorstComments(string $order, int $max = 5): array
   {
      return
         $this->manager
                  ->createQuery("SELECT c FROM App\Entity\Comment c GROUP BY c ORDER BY c.rating $order")
                  ->setMaxResults($max)
                  ->getResult();
   }

   /**
    * Permet de récupérer les derniers inscrivants
    *
    * @param integer $max
    * @return array
    */
   private function getLastUsers(int $max): array
   {
      return 
         $this->manager
                  ->createQuery("SELECT u, AVG(c.rating) rating FROM App\Entity\User u JOIN u.posts p JOIN p.comments c GROUP BY u ORDER BY u.createdAt DESC")
                  ->setMaxResults($max)
                  ->getResult();
   }

   /**
    * Permet de récupérer les derniers postes
    *
    * @param integer $max
    * @return array
    */
   private function getLastPosts(int $max): array
   {
      return 
         $this->manager
                  ->createQuery("SELECT p, AVG(c.rating) rating FROM App\Entity\Post p JOIN p.comments c GROUP BY p ORDER BY p.createdAt DESC")
                  ->setMaxResults($max)
                  ->getResult();
   }

   /**
    * Sert à renvoyer les dernies commentaires
    *
    * @param integer $max
    * @return array
    */
   private function getLastComments(int $max): array
   {
      return 
         $this->manager
                  ->createQuery("SELECT c FROM App\Entity\Comment c GROUP BY c ORDER BY c.createdAt DESC")
                  ->setMaxResults($max)
                  ->getResult();
   }

}
