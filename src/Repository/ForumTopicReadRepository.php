<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\ForumTopicRead;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ForumTopicRead>
 *
 * @method ForumTopicRead|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumTopicRead|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumTopicRead[]    findAll()
 * @method ForumTopicRead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumTopicReadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumTopicRead::class);
    }

    public function save(ForumTopicRead $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ForumTopicRead $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return ForumTopicRead[] Returns an array of ForumTopicRead objects
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

    //    public function findOneBySomeField($value): ?ForumTopicRead
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
