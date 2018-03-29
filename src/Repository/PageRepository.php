<?php

namespace App\Repository;

use App\Entity\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Page::class);
    }

    
    /**
     * @return Page Returns a Page Object by contract and page number
     */
    public function findPageByPageNum($contract,$num): ?Page
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.contract = :contract')
            ->andWhere('p.num = :num')
            ->setParameter('contract', $contract)
            ->setParameter('num', $num)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
}
