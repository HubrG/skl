<?php

namespace App\Repository;

use App\Entity\InboxGroup;
use App\Entity\InboxGroupMember;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<InboxGroupMember>
 *
 * @method InboxGroupMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method InboxGroupMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method InboxGroupMember[]    findAll()
 * @method InboxGroupMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InboxGroupMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InboxGroupMember::class);
    }

    public function save(InboxGroupMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(InboxGroupMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return InboxGroupMember[] Returns an array of InboxGroupMember objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?InboxGroupMember
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
