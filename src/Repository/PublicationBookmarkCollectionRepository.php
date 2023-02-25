<?php

namespace App\Repository;

use App\Entity\PublicationBookmarkCollection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PublicationBookmarkCollection>
 *
 * @method PublicationBookmarkCollection|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicationBookmarkCollection|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicationBookmarkCollection[]    findAll()
 * @method PublicationBookmarkCollection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationBookmarkCollectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublicationBookmarkCollection::class);
    }

    public function save(PublicationBookmarkCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PublicationBookmarkCollection $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PublicationBookmarkCollection[] Returns an array of PublicationBookmarkCollection objects
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

//    public function findOneBySomeField($value): ?PublicationBookmarkCollection
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
