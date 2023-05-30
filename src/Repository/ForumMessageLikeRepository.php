<?php

namespace App\Repository;

use App\Entity\ForumMessageLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ForumMessageLike>
 *
 * @method ForumMessageLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumMessageLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumMessageLike[]    findAll()
 * @method ForumMessageLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumMessageLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumMessageLike::class);
    }

    public function save(ForumMessageLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ForumMessageLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ForumMessageLike[] Returns an array of ForumMessageLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ForumMessageLike
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
