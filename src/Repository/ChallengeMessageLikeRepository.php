<?php

namespace App\Repository;

use App\Entity\ChallengeMessageLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChallengeMessageLike>
 *
 * @method ChallengeMessageLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChallengeMessageLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChallengeMessageLike[]    findAll()
 * @method ChallengeMessageLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChallengeMessageLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChallengeMessageLike::class);
    }

    public function save(ChallengeMessageLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ChallengeMessageLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ChallengeMessageLike[] Returns an array of ChallengeMessageLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ChallengeMessageLike
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
