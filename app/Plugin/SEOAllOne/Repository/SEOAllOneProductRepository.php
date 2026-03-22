<?php

namespace Plugin\SEOAllOne\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\SEOAllOne\Entity\SEOAllOneProduct;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SEOAllOneProductRepository extends AbstractRepository{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SEOAllOneProduct::class);
    }

    public function get($id = 1)
    {
        return $this->find($id);
    }
}


?>