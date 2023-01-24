<?php

namespace App\Repository;

use App\Entity\PublicationChapterVersioning;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationChapterVersioning>
 *
 * @method PublicationChapterVersioning|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationChapterVersioning|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationChapterVersioning[]    findAll()
 * @method PublicationChapterVersioning[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationChapterVersioningRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationChapterVersioning::class);
    }

    public function save(PublicationChapterVersioning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationChapterVersioning $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublicationChapterVersioning[] Returns an array of PublicationChapterVersioning objects
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

//    public function findOneBySomeField($value): ?PublicationChapterVersioning
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
