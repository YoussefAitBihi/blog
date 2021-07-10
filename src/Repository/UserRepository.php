<?php

namespace App\Repository;

use Exception;
use PDO;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Sert à retourner tous les infos pour chaque utilisateur
     *
     * @param integer $id
     * @return array
     */
    public function getInfosUser(int $id): array
    {   
        // Count Posts
        $posts = $this->getCountPostsUser($id);
        // Count Comments
        $comments = $this->getCountCommentsUser($id);
        // Rating
        $rating = $this->getRating($id);

        return compact("posts", "comments", "rating");
    }

    /**
     * Sert à retourner le nombre de postes
     *
     * @param integer $id
     * @return integer
     */
    private function getCountPostsUser(int $id): int
    {
        $posts = $this->createQueryBuilder('u')
                    ->select('COUNT(p) as posts')
                    ->join('u.posts', 'p')
                    ->where("u.id = :user_id")
                    ->setParameter('user_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();

        return (int) $posts[0]['posts'];
    }

    /**
     * Sert à calculer le nombre de commentaire pour chaque utilisateur
     *
     * @param integer $id
     * @return integer
     */
    private function getCountCommentsUser(int $id): int
    {
        $comments = $this->createQueryBuilder('u')
                        ->select('COUNT(c) as comments')
                        ->join('u.comments', 'c')
                        ->where('u.id = :user_id')
                        ->setParameter('user_id', $id, PDO::PARAM_INT)
                        ->getQuery()
                        ->getResult();

        return (int) $comments[0]['comments'];
    }

    /**
     * A pour but de calculer le feed-back pour chaque utilisateur
     *
     * @param integer $id
     * @return float
     */
    private function getRating(int $id): float
    {
        $rating = $this->createQueryBuilder('u')
                    ->select('AVG(c.rating) as rating')
                    ->join('u.posts', 'p')
                    ->join('p.comments', 'c')
                    ->where('u.id = :user_id')
                    ->setParameter('user_id', $id, PDO::PARAM_INT)
                    ->getQuery()
                    ->getResult();
        
        return (float) $rating[0]['rating'];
    }

    /**
     * Sert à renvoyer les meilleurs utilisateurs par son rating
     *
     * @param integer $max contenant la limite d'utilisateurs
     * @return array
     */
    public function getTopUsers(int $max = 4): array
    {
        if (func_num_args() > 1):
            throw new Exception("Vous n'avez le droit que de passer un seul argument");
        endif;

        return 
            $this->createQueryBuilder('u')
                ->select('u as users', 'AVG(c.rating) as rating', 'COUNT(p.id) as posts')
                ->join('u.posts', 'p')
                ->join('p.comments', 'c')
                ->groupBy('u')
                ->orderBy('rating', 'DESC')
                ->setMaxResults($max)
                ->getQuery()
                ->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
