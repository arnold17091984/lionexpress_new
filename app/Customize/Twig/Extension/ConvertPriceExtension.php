<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConvertPriceExtension extends AbstractExtension
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    )
    {
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('convertPrice', function ($name, $unit, $total_price) {
                $count = preg_replace('/' . preg_quote($unit, '/') . '/', "", $name);
                $count = intval($count);
                if ($count <= 0) {
                    return $total_price;
                }
                return $total_price / $count;
            }),
        ];
    }

}