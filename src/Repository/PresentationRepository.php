<?php

namespace App\Repository;

use App\Entity\Presentation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Presentation>
 */
class PresentationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presentation::class);
    }

    public function get(): Presentation
    {
        return $this->find(1) ?? new Presentation();
    }

    public function save(Presentation $presentation): void
    {
        $this->getEntityManager()->persist($presentation);
        $this->getEntityManager()->flush();
    }
}
