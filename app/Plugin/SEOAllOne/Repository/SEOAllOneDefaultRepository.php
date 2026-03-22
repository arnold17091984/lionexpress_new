<?php

namespace Plugin\SEOAllOne\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\SEOAllOne\Entity\SEOAllOneDefault;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SEOAllOneDefaultRepository extends AbstractRepository{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SEOAllOneDefault::class);
    }

    public function get($id = 1)
    {
        return $this->find($id);
    }
}


?>