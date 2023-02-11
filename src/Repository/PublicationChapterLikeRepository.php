<?php

namespace App\Repository;

use App\Entity\PublicationChapterLike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationChapterLike>
 *
 * @method PublicationChapterLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationChapterLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationChapterLike[]    findAll()
 * @method PublicationChapterLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationChapterLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationChapterLike::class);
    }

    public function save(PublicationChapterLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationChapterLike $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublicationChapterLike[] Returns an array of PublicationChapterLike objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PublicationChapterLike
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
