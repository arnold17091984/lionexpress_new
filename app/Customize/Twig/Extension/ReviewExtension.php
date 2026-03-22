<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Repository\OrderRepository;
use Plugin\ProductReview4\Repository\ProductReviewRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Unified Twig extension for product review-related functions.
 *
 * Consolidates the following individual extensions into one:
 * - AvgReviewExtension  (getAvgReview)
 * - GetReviewExtension  (getReview)
 * - GetStarExtension    (getStar)
 * - IsReviewExtension   (isReview)
 * - MaxReviewExtension  (getMaxReview)
 * - MinReviewExtension  (getMinReview)
 *
 * All Twig function names remain identical for template compatibility.
 */
class ReviewExtension extends AbstractExtension
{
    /**
     * @var ProductReviewRepository
     */
    private $productReviewRepository;

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
        ProductReviewRepository $productReviewRepository,
        OrderRepository $orderRepository,
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        $this->productReviewRepository = $productReviewRepository;
        $this->orderRepository = $orderRepository;
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getAvgReview', [$this, 'getAvgReview']),
            new TwigFunction('getReview', [$this, 'getReview']),
            new TwigFunction('getStar', [$this, 'getStar'], ['pre_escape' => 'html', 'is_safe' => ['html']]),
            new TwigFunction('isReview', [$this, 'isReview']),
            new TwigFunction('getMaxReview', [$this, 'getMaxReview']),
            new TwigFunction('getMinReview', [$this, 'getMinReview']),
        ];
    }

    /**
     * Get the average review score and count for a product.
     *
     * @param int $productId
     * @return array|string An associative array with 'star' and 'cnt' keys, or empty string if no reviews
     */
    public function getAvgReview($productId)
    {
        $qb = $this->productReviewRepository->createQueryBuilder('pr');
        $qb
            ->select('avg(pr.recommend_level) as star, count(pr.id) as cnt')
            ->andWhere('pr.Product = :product_id')
            ->setParameter('product_id', $productId)
            ->groupBy('pr.Product')
            ->orderBy('pr.create_date', 'DESC');

        $qb->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result ? $result[0] : '';
    }

    /**
     * Get review breakdown by recommend level for a product.
     *
     * @param int $productId
     * @return array Associative array keyed by recommend_level with 'cnt' and 'meter' values
     */
    public function getReview($productId)
    {
        $qb = $this->productReviewRepository->createQueryBuilder('pr');
        $qb->select('pr.recommend_level, count(pr.recommend_level) cnt')
           ->andWhere('pr.Product = :product_id')
           ->setParameter('product_id', $productId)
           ->groupBy('pr.recommend_level')
           ->orderBy('pr.recommend_level', 'DESC');

        $list = $qb->getQuery()->getResult();

        $total = 0;
        if (count($list) != 0) {
            $qb2 = $this->productReviewRepository->createQueryBuilder('pr2');
            $qb2->select('count(pr2.id)')
                ->andWhere('pr2.Product = :product_id')
                ->setParameter('product_id', $productId);

            $total = $qb2->getQuery()->getSingleScalarResult();
        }

        $result = [];
        foreach ($list as $item) {
            $result[$item['recommend_level']] = [
                'cnt' => $item['cnt'],
                'meter' => floor($item['cnt'] / $total * 100),
            ];
        }

        return $result;
    }

    /**
     * Render star rating HTML from a numeric score.
     *
     * @param float $good Rating value (0-5, may include decimals)
     * @return string HTML string of star icons
     */
    public function getStar($good)
    {
        $positiveStars = [
            '',
            '<i class="fas fa-star"></i>',
            '<i class="fas fa-star"></i><i class="fas fa-star"></i>',
            '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>',
            '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>',
            '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>',
        ];
        $negativeStars = [
            '',
            '<i class="far fa-star"></i>',
            '<i class="far fa-star"></i><i class="far fa-star"></i>',
            '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>',
            '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>',
            '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>',
        ];
        $halfStar = '<i class="fas fa-star-half-alt"></i>';

        $goodInteger = floor($good);
        $fraction = $good - $goodInteger;
        $badInteger = 5 - $goodInteger;

        if ($fraction > 0) {
            return $positiveStars[$goodInteger] . $halfStar . $negativeStars[$badInteger - 1];
        }

        return $positiveStars[$goodInteger] . $negativeStars[$badInteger];
    }

    /**
     * Check if a customer has purchased a product (to determine review eligibility).
     *
     * Checks orders with the following statuses:
     *   1 = New order
     *   4 = Paid
     *   5 = Preparing for shipment
     *   6 = Shipped
     *   9 = Delivered
     *
     * @param mixed $Product Product entity or ID
     * @param mixed $Customer Customer entity or ID
     * @return int Number of matching orders
     */
    public function isReview($Product, $Customer)
    {
        // EC-CUBE OrderStatus IDs:
        // 1 = New, 4 = Paid, 5 = Preparing, 6 = Shipped, 9 = Delivered
        $validOrderStatusIds = [1, 4, 5, 6, 9];

        $qb = $this->orderRepository->createQueryBuilder('o');
        $qb->select('count(o.id)')
           ->andWhere('o.Customer = :Customer')
           ->setParameter('Customer', $Customer)
           ->andWhere('o.OrderStatus in(:statuses)')
           ->setParameter('statuses', $validOrderStatusIds)
           ->innerJoin('o.OrderItems', 'oi')
           ->andWhere('oi.Product = :Product')
           ->setParameter('Product', $Product);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Get the most recent positive review (recommend_level 3-5) for a product.
     *
     * @param int $productId
     * @return object|null ProductReview entity or null
     */
    public function getMaxReview($productId)
    {
        $qb = $this->productReviewRepository->createQueryBuilder('pr');
        $qb
            ->andWhere('pr.Product = :product_id')
            ->setParameter('product_id', $productId)
            ->andWhere('pr.recommend_level in (3,4,5)')
            ->orderBy('pr.create_date', 'DESC');

        $qb->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result ? $result[0] : null;
    }

    /**
     * Get the most recent negative review (recommend_level 1-2) for a product.
     *
     * @param int $productId
     * @return object|null ProductReview entity or null
     */
    public function getMinReview($productId)
    {
        $qb = $this->productReviewRepository->createQueryBuilder('pr');
        $qb
            ->andWhere('pr.Product = :product_id')
            ->setParameter('product_id', $productId)
            ->andWhere('pr.recommend_level in (1,2)')
            ->orderBy('pr.create_date', 'DESC');

        $qb->setMaxResults(1);
        $result = $qb->getQuery()->getResult();

        return $result ? $result[0] : null;
    }
}
