<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * Retourne le nombre total d'utilisateurs
     *
     * @return int
     */
    public function count()
    {
        return $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return array
     */
    public function findAllForPage($page = 1, $limit = 10)
    {
        $offset = $page * $limit - $limit;

        return $this->findBy([], ['username' => 'ASC'], $limit, $offset);
    }

    /**
     * @return null|object
     */
    public function findAny()
    {
        return $this->findOneBy([]);
    }

    /**
     * @param string $filter
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function findAllQueryBuilder($filter = '')
    {
        $qb = $this->createQueryBuilder('m')
            ->orderBy('m.id', 'ASC');

        if ($filter) {
            $qb->andWhere('m.username LIKE :filter OR m.email LIKE :filter')
                ->setParameter('filter', '%'.$filter.'%');
        }

        return $qb;
    }
}