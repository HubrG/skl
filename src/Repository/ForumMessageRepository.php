<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\ForumTopic;
use App\Entity\ForumMessage;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ForumMessage>
 *
 * @method ForumMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ForumMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ForumMessage[]    findAll()
 * @method ForumMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ForumMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ForumMessage::class);
    }

    public function save(ForumMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ForumMessage $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getUnreadMessageCountForUser(User $user, ForumTopic $topic): int
    {
        $entityManager = $this->getEntityManager();

        // Obtenez la dernière date de lecture du topic par l'utilisateur
        $subQuery = $entityManager->createQueryBuilder()
            ->select('MAX(ftr.readAt)')
            ->from('App\Entity\ForumTopicRead', 'ftr')
            ->where('ftr.user = :user')
            ->andWhere('ftr.topic = :topic')
            ->getDQL();

        $query = $this->createQueryBuilder('fm')
            ->select('COUNT(fm.id)')
            ->where('fm.topic = :topic')
            ->andWhere('fm.published_at > (' . $subQuery . ')')
            ->setParameters([
                'user' => $user,
                'topic' => $topic,
            ])
            ->getQuery();

        return (int) $query->getSingleScalarResult();
    }

    //    /**
    //     * @return ForumMessage[] Returns an array of ForumMessage objects
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

    //    public function findOneBySomeField($value): ?ForumMessage
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
