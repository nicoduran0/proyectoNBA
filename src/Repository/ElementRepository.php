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

    /**
     * Busca jugadores por nombre o equipo
     * @param string $term El término de búsqueda
     * @return Element[] Devuelve un array de objetos Element
     */
    public function searchByNameOrTeam(string $term): array
    {
        return $this->createQueryBuilder('e')
            // Buscamos si el nombre O el equipo contienen el texto
            ->andWhere('e.name LIKE :term OR e.team LIKE :term')
            // Añadimos % para buscar texto parcial (ej: "urr" encuentra "Curry")
            ->setParameter('term', '%' . $term . '%')
            // Ordenamos alfabéticamente para que quede mejor
            ->orderBy('e.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Element[] Returns an array of Element objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Element
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
