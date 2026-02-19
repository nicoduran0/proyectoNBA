<?php

namespace App\Repository;

use App\Entity\Element;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Element>
 */
class ElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Element::class);
    }

    public function searchByNameOrTeam(string $term): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.name LIKE :term OR e.team LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
