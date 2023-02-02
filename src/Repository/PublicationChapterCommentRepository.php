<?php

namespace App\Repository;

use App\Entity\PublicationChapterComment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationChapterComment>
 *
 * @method PublicationChapterComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationChapterComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationChapterComment[]    findAll()
 * @method PublicationChapterComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationChapterCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationChapterComment::class);
    }

    public function save(PublicationChapterComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationChapterComment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublicationChapterComment[] Returns an array of PublicationChapterComment objects
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

//    public function findOneBySomeField($value): ?PublicationChapterComment
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
