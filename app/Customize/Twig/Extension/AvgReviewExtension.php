<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Plugin\ProductReview4\Repository\ProductReviewRepository;

class AvgReviewExtension extends AbstractExtension
{
    /**
     * @var ProductReviewRepository
     */
    private $productReviewRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(
        ProductReviewRepository $productReviewRepository,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    )
    {
        $this->productReviewRepository = $productReviewRepository;
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAvgReview', function ($product_id) {

                $qb = $this->productReviewRepository
                    ->createQueryBuilder('pr');
                $qb
                    ->select('avg(pr.recommend_level) as star, count(pr.id) as cnt')
                    ->andWhere('pr.Product = :product_id')
                    ->setParameter('product_id', $product_id)
                    ->groupBy('pr.Product')
                    ->orderBy('pr.create_date', 'DESC');

                $qb->setMaxResults(1);
                $result = $qb->getQuery()->getResult();
                if ($result) {
                    return $result[0];
                } else {
                    return '';
                }
            }),
        ];
    }
}