<?php

namespace Plugin\ApgExtendCartIn;

use Eccube\Entity\Product;
use Eccube\Event\TemplateEvent;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;
use Plugin\ApgExtendCartIn\Entity\Domain\ConfigSettingType;
use Plugin\ApgExtendCartIn\Repository\ConfigRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Event implements EventSubscriberInterface
{

    const TEMPLATE_NAMESPACE = '@ApgExtendCartIn';

    /** @var \Twig_Environment */
    protected $twig;

    /** @var ConfigRepository */
    protected $configRepository;

    public function __construct(
        \Twig_Environment $twig,
        ConfigRepository $configRepository
    )
    {
        $this->twig = $twig;
        $this->configRepository = $configRepository;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'onRenderProductDetail',
        ];

    }

    public function onRenderProductDetail(TemplateEvent $event)
    {
        $source = $event->getSource();

        // data
        $parameters = $event->getParameters();
        // setting
        $loader = $this->twig->getLoader();
        $config = $this->configRepository->getOrNew();

        /** @var Product $Product */
        $Product = $parameters['Product'];

        if ($config->getSettingType() === ConfigSettingType::INDIVIDUAL) {
            $cartType = $Product->getApgCartType();
        } else {
            $cartType = $config->getCartType();
        }

        if ($Product->hasProductClass()) {

            if ($cartType === CartType::LIST) {

                $pattern = '|<form action="{{ url\(\'product_add_cart\', {id:Product.id}\) }}"(.*?)>|s';
                $addRow = $this->twig->getLoader()->getSourceContext('Block/plg_cart_in_list.twig')->getCode();
                if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
                    $replacement = $addRow . $matches[0][0];
                    $source = preg_replace($pattern, $replacement, $source);
                }


                $parameters['cartType'] = $cartType;
                $event->setSource($source);
                $event->setParameters($parameters);

                $event->addAsset(self::TEMPLATE_NAMESPACE . '/front/add_cart_list_css.twig');
                $event->addSnippet(self::TEMPLATE_NAMESPACE . '/front/add_cart_list_js.twig');

            } elseif ($cartType === CartType::GRID) {

                // data
                $pattern = '|<form action="{{ url\(\'product_add_cart\', {id:Product.id}\) }}"(.*?)>|s';
                $addRow = $this->twig->getLoader()->getSourceContext('Block/plg_cart_in_grid.twig')->getCode();
                if (preg_match($pattern, $source, $matches, PREG_OFFSET_CAPTURE)) {
                    $replacement = $addRow . $matches[0][0];
                    $source = preg_replace($pattern, $replacement, $source);
                }

                $parameters['cartType'] = $cartType;
                $parameters['classCategoryNames1'] = $Product->getClassCategoryNames1();
                $parameters['classCategoryNames2'] = $Product->getClassCategoryNames2();

                $event->setSource($source);
                $event->setParameters($parameters);

                $event->addAsset(self::TEMPLATE_NAMESPACE . '/front/add_cart_grid_css.twig');
                $event->addSnippet(self::TEMPLATE_NAMESPACE . '/front/add_cart_grid_js.twig');

            }

        }

    }
}
