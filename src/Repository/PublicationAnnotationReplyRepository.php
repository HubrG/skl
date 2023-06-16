<?php

namespace App\Repository;

use App\Entity\PublicationAnnotationReply;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationAnnotationReply>
 *
 * @method PublicationAnnotationReply|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationAnnotationReply|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationAnnotationReply[]    findAll()
 * @method PublicationAnnotationReply[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationAnnotationReplyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationAnnotationReply::class);
    }

    public function save(PublicationAnnotationReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationAnnotationReply $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublicationAnnotationReply[] Returns an array of PublicationAnnotationReply objects
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

//    public function findOneBySomeField($value): ?PublicationAnnotationReply
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
