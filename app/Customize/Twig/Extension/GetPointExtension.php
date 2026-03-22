<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetPointExtension extends AbstractExtension
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

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getPoint', function ($price) {

                $BaseInfo = $this->entityManager->getRepository('Eccube\Entity\BaseInfo')->find(1);
                $rate = $BaseInfo->getBasicPointRate();
                return floor($price * ($rate / 100));
            }),
        ];
    }
}