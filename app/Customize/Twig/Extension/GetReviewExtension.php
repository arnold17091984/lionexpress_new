<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Plugin\ProductReview4\Repository\ProductReviewRepository;

class GetReviewExtension extends AbstractExtension
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
            new TwigFunction('getReview', function ($product_id) {

                $qb = $this->productReviewRepository->createQueryBuilder('pr');
                $qb->select('pr.recommend_level, count(pr.recommend_level) cnt')
                   ->andWhere('pr.Product = :product_id')
                   ->setParameter('product_id', $product_id)
                   ->groupBy('pr.recommend_level')
                   ->orderBy('pr.recommend_level', 'DESC');

                $list = $qb->getQuery()->getResult();

                $total = 0;
                if (count($list) != 0) {
                    $qb2 = $this->productReviewRepository->createQueryBuilder('pr2');
                    $qb2->select('count(pr2.id)')
                        ->andWhere('pr2.Product = :product_id')
                        ->setParameter('product_id', $product_id);

                    $total = $qb2->getQuery()->getSingleScalarResult();
                }

                $result = [];
                foreach ($list as $item) {
                    $result[$item['recommend_level']] = array('cnt' => $item['cnt'], 'meter' => floor($item['cnt'] / $total * 100));
                }
                return $result;

            }),
        ];
    }
}