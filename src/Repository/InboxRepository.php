<?php

namespace App\Repository;

use App\Entity\Inbox;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Inbox>
 *
 * @method Inbox|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inbox|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inbox[]    findAll()
 * @method Inbox[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InboxRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inbox::class);
    }

    public function save(Inbox $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Inbox $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // src/Repository/InboxRepository.php

    // public function findDistinctUserToByUser($user)
    // {
    //     $entityManager = $this->getEntityManager();


    //     $sql = '
    //     SELECT i FROM App\Entity\Inbox i
    //     WHERE i.id IN (
    //         SELECT MAX(inbox.id) FROM App\Entity\Inbox inbox
    //         WHERE (inbox.user = :user OR inbox.UserTo = :user)
    //     )
    // ';

    //     $query = $entityManager->createQuery($sql);
    //     $query->setParameter('user', $user);

    //     return $query->getResult();
    // }

    //    /**
    //     * @return Inbox[] Returns an array of Inbox objects
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

    //    public function findOneBySomeField($value): ?Inbox
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
