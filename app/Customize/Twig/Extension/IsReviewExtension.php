<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Eccube\Repository\OrderRepository;

class IsReviewExtension extends AbstractExtension
{
    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    )
    {
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('isReview', function ($Product, $Customer) {

                $qb = $this->orderRepository->createQueryBuilder('o');
                $qb->select('count(o.id)')
                   ->andWhere('o.Customer = :Customer')
                   ->setParameter('Customer', $Customer)
                   // EC-CUBE OrderStatus IDs: 1=New, 4=Paid, 5=Preparing, 6=Shipped, 9=Delivered
                   ->andWhere('o.OrderStatus in(1,4,5,6,9)')
                   ->innerJoin('o.OrderItems', 'oi')
                   ->andWhere('oi.Product = :Product')
                   ->setParameter('Product', $Product);

                return $qb->getQuery()->getSingleScalarResult();
            }),
        ];
    }
}