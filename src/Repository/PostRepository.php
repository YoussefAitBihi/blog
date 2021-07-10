<?php

namespace App\Repository;

use Exception;
use PDO;
use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository {

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * Sert à renvoyer top de postes
     *
     * @param int $max
     * @return Post[]
     */
    public function getTopPosts(int $max = 4): array
    {
        if (func_num_args() > 1):
            throw new Exception("Vous n'avez le droit que de passer un seul argument");
        endif;

        return $this->createQueryBuilder('p')
                    ->select('p as posts', 'AVG(c.rating) as rating')
                    ->join('p.comments', 'c')
                    ->groupBy('p')
                    ->orderBy('rating', 'DESC')
                    ->setMaxResults($max)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Sert à retourner les infos pour chaque post
     *
     * @param integer $id
     * @return integer|null
     */
    public function getInfosPost(int $id): ?array
    {
        if (func_num_args() > 1):
            throw new Exception("Vous n'avez le droit que passer un seul argument");
        endif;

        $counts = $this->createQueryBuilder('p')
            ->select('COUNT(c) as comments', 'AVG(c.rating) as rating')
            ->join('p.comments', 'c')
            ->where("p.id = :post_id")
            ->setParameter(':post_id', $id, PDO::PARAM_INT)
            ->getQuery()
            ->getResult();

        return $counts[0];
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
