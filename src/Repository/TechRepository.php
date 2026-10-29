<?php

namespace App\Repository;

use App\Entity\Side;
use App\Entity\Tech;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tech>
 */
class TechRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tech::class);
    }

    /**
     * The techs of the stack slide, in their order.
     *
     * @return list<Tech>
     */
    public function stack(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    /**
     * The techs of one side of the stack slide, in their order.
     *
     * @return list<Tech>
     */
    public function side(Side $side): array
    {
        return $this->findBy(['side' => $side], ['id' => 'ASC']);
    }
}
