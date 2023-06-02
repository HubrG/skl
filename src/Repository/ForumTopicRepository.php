<?php

namespace App\Repository;

use App\Entity\ForumTopic;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ForumTopic>
 *
 * @method ForumTopic|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumTopic|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumTopic[]    findAll()
 * @method ForumTopic[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumTopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumTopic::class);
    }

    public function save(ForumTopic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ForumTopic $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function findByQuery(string $query): array
    {

        if (empty($query)) {
            return [];
        }
        return $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('t.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
    //    /**
    //     * @return ForumTopic[] Returns an array of ForumTopic objects
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

    //    public function findOneBySomeField($value): ?ForumTopic
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
