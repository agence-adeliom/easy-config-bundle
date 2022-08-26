<?php

namespace Adeliom\EasyConfigBundle\Repository;

use Adeliom\EasyConfigBundle\Entity\Config;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Config|null find($id, $lockMode = null, $lockVersion = null)
 * @method Config|null findOneBy(array $criteria, array $orderBy = null)
 * @method Config[]    findAll()
 * @method Config[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfigRepository extends ServiceEntityRepository
{
    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByKey($key)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->where('c.key = :key')
            ->setParameter('key', $key);

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }
}
