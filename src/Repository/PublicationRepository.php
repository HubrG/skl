<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Publication;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Publication>
 *
 * @method Publication|null find($id, $lockMode = null, $lockVersion = null)
 * @method Publication|null findOneBy(array $criteria, array $orderBy = null)
 * @method Publication[]    findAll()
 * @method Publication[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Publication::class);
    }

    public function save(Publication $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Publication $entity, bool $flush = false): void
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
        return $this->createQueryBuilder('p')
            ->join('p.publicationChapters', 'pch') // Ici, 'publicationChapters' devrait être le nom de la propriété dans votre entité "Publication" qui fait référence à vos chapitres.
            ->andWhere('p.title LIKE :query')
            ->andWhere('p.status = 2')
            ->andWhere('p.challenge IS NULL')
            ->andWhere('pch.status = 2') // Et ici nous ajoutons le critère pour le statut du chapitre
            ->andWhere("p.hideSearch = FALSE")
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function findByQueryChallenge(string $query): array
    {
        if (empty($query)) {
            return [];
        }
        return $this->createQueryBuilder('p')
            ->join('p.publicationChapters', 'pch') // Ici, 'publicationChapters' devrait être le nom de la propriété dans votre entité "Publication" qui fait référence à vos chapitres.
            ->andWhere('p.title LIKE :query')
            ->andWhere('p.status = 2')
            ->andWhere('p.challenge IS NOT NULL')
            ->andWhere('pch.status = 2') // Et ici nous ajoutons le critère pour le statut du chapitre
            ->andWhere("p.hideSearch = FALSE")
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Publication[] Returns an array of Publication objects
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

    //    public function findOneBySomeField($value): ?Publication
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
