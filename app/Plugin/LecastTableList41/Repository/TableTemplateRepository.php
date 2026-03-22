<?php
/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2018 lecast system.
  * @author Tetsuji Shiro 
  *
  * このプラグインは再販売禁止です。
  */
namespace Plugin\LecastTableList41\Repository;

use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Plugin\LecastTableList41\Entity\TableTemplate;

class TableTemplateRepository extends AbstractRepository
{
    /**
     * TableTemplateRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, TableTemplate::class);
    }

    /**
     * 登録.
     *
     * @param TableTemplate $TableTemplate
     */
    public function save($TableTemplate)
    {
        $em = $this->getEntityManager();
        $em->persist($TableTemplate);
        $em->flush($TableTemplate);
    }

    /**
     * 削除.
     *
     * @param TableTemplate $TableTemplate
     */
    public function remove($TableTemplate)
    {
        $em = $this->getEntityManager();
        $em->remove($TableTemplate);
        $em->flush($TableTemplate);
    }

}