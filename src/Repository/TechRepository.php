<?php

namespace App\Repository;

use App\Entity\Side;
use App\Entity\Tech;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TechRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tech::class);
    }

    public function stack(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    public function side(Side $side): array
    {
        return $this->findBy(['side' => $side], ['id' => 'ASC']);
    }
}
