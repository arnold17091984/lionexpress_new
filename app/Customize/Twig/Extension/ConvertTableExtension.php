<?php

namespace Customize\Twig\Extension;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ConvertTableExtension extends AbstractExtension
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
            new TwigFunction('convertTable', function ($data) {
                // Sanitize input: only allow table-related HTML tags
                $sanitized = strip_tags($data, '<tr><th><td><table><tbody><thead>');
                $convert = preg_replace("/<tr>/", '<div class="ct">', $sanitized);
                $convert = preg_replace("/<\/tr>/", '</div>', $convert);
                $convert = preg_replace("/<th>/", '<div class="ttitle">', $convert);
                $convert = preg_replace("/<\/th>/", '</div>', $convert);
                $convert = preg_replace("/<td>/", '<div class="tvalue">', $convert);
                $convert = preg_replace("/<\/td>/", '</div>', $convert);
                // Remove remaining table wrapper tags
                $convert = preg_replace("/<\/?(?:table|tbody|thead)>/", '', $convert);
                return $convert;
            }, ['pre_escape' => 'html', 'is_safe' => ['html']]),
        ];
    }

}