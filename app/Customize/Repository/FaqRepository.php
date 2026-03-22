<?php

namespace Customize\Repository;

use Customize\Entity\Faq;
use Eccube\Repository\AbstractRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class FaqRepository extends AbstractRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Faq::class);
    }

    public function save(Faq $faq): void
    {
        $em = $this->getEntityManager();
        $em->persist($faq);
        $em->flush();
    }

    public function delete(Faq $faq): void
    {
        $em = $this->getEntityManager();
        $em->remove($faq);
        $em->flush();
    }

    /**
     * @return Faq[]
     */
    public function findAllVisible(): array
    {
        return $this->findBy(['visible' => true], ['sort_no' => 'ASC']);
    }

    /**
     * @return Faq[]
     */
    public function findAllOrdered(): array
    {
        return $this->findBy([], ['sort_no' => 'ASC']);
    }
}
