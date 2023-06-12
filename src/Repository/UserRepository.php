<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }
    public function findByQuery(string $query): array
    {

        if (empty($query)) {
            return [];
        }
        return $this->createQueryBuilder('u')
            ->andWhere('u.username LIKE :query')
            ->orWhere(('u.nickname LIKE :query'))
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }
    public function userAccessFindByQuery(string $query, int $idPub, int $ownerId): array
    {
        if (empty($query)) {
            return [];
        }

        // Créer une sous-requête pour trouver les utilisateurs qui ont déjà accès à la publication spécifique
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->select('IDENTITY(pa.user)')
            ->from('App\Entity\PublicationAccess', 'pa')
            ->where('pa.publication = :idPub');

        // Créer la requête principale qui trouve les utilisateurs basés sur le nom d'utilisateur ou le surnom, 
        // tout en excluant ceux qui ont déjà accès à la publication spécifique et le propriétaire de la publication
        $mainQuery = $this->createQueryBuilder('u');
        $mainQuery->where(
            $mainQuery->expr()->orX(
                $mainQuery->expr()->like('u.username', ':query'),
                $mainQuery->expr()->like('u.nickname', ':query')
            )
        )
            ->andWhere($mainQuery->expr()->not($mainQuery->expr()->in('u.id', $subQuery->getDQL())))
            ->andWhere('u.id != :ownerId')
            ->orderBy('u.username', 'ASC')
            ->setMaxResults(5)
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('idPub', $idPub)
            ->setParameter('ownerId', $ownerId);

        return $mainQuery->getQuery()->getResult();
    }
    public function supportFindByQuery(string $query, int $idPub, int $ownerId): array
    {
        if (empty($query)) {
            return [];
        }

        // Créer une sous-requête pour trouver les utilisateurs qui ont déjà accès à la publication spécifique
        $subQuery = $this->getEntityManager()->createQueryBuilder();
        $subQuery->select('IDENTITY(pa.user)')
            ->from('App\Entity\PublicationSupport', 'pa')
            ->where('pa.publication = :idPub');

        // Créer la requête principale qui trouve les utilisateurs basés sur le nom d'utilisateur ou le surnom, 
        // tout en excluant ceux qui ont déjà accès à la publication spécifique et le propriétaire de la publication
        $mainQuery = $this->createQueryBuilder('u');
        $mainQuery->where(
            $mainQuery->expr()->orX(
                $mainQuery->expr()->like('u.username', ':query'),
                $mainQuery->expr()->like('u.nickname', ':query')
            )
        )
            ->andWhere($mainQuery->expr()->not($mainQuery->expr()->in('u.id', $subQuery->getDQL())))
            ->andWhere('u.id != :ownerId')
            ->orderBy('u.username', 'ASC')
            ->setMaxResults(5)
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('idPub', $idPub)
            ->setParameter('ownerId', $ownerId);

        return $mainQuery->getQuery()->getResult();
    }



    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
