<?php

namespace Plugin\SEOAllOne\Repository;

use Eccube\Repository\AbstractRepository;
use Plugin\SEOAllOne\Entity\SEOAllOneCategory;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SEOAllOneCategoryRepository extends AbstractRepository{

    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SEOAllOneCategory::class);
    }

    public function get($id = 1)
    {
        return $this->find($id);
    }
}


?>