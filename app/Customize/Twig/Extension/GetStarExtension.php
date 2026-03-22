<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

use Plugin\ProductReview4\Repository\ProductReviewRepository;

class GetStarExtension extends AbstractExtension
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
            new TwigFunction('getStar', function ($good) {

                $positive_stars = array('', '<i class="fas fa-star"></i>', '<i class="fas fa-star"></i><i class="fas fa-star"></i>', '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>', '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>', '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>');
                $negative_stars = array('', '<i class="far fa-star"></i>', '<i class="far fa-star"></i><i class="far fa-star"></i>', '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>', '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>', '<i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i><i class="far fa-star"></i>');
                $half_star = '<i class="fas fa-star-half-alt"></i>';
                $goodInteger = floor($good);
                $fraction = $good - $goodInteger;
                $badInteger = 5 - $goodInteger;
                $result = '';
                if ($fraction > 0) {
                    $result = $positive_stars[$goodInteger] . $half_star . $negative_stars[$badInteger - 1];
                } else {
                    $result = $positive_stars[$goodInteger] . $negative_stars[$badInteger];
                }
                return $result;
            }, ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }
}