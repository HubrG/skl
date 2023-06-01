<?php

namespace App\Repository;

use App\Entity\PublicationChapter;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<PublicationChapter>
 *
 * @method PublicationChapter|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationChapter|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationChapter[]    findAll()
 * @method PublicationChapter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationChapterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationChapter::class);
    }

    public function save(PublicationChapter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationChapter $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findChaptersByPublicationAndStatus($publicationId, $status)
    {
        $qb = $this->createQueryBuilder('pc')
            ->andWhere('pc.publication = :publicationId')
            ->andWhere('pc.status = :status')
            ->setParameter('publicationId', $publicationId)
            ->setParameter('status', $status)
            ->orderBy('pc.order_display', 'ASC');

        return $qb->getQuery()->getResult();
    }


    //    /**
    //     * @return PublicationChapter[] Returns an array of PublicationChapter objects
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

    //    public function findOneBySomeField($value): ?PublicationChapter
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
