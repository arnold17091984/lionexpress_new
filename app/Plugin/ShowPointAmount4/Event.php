<?php

/*
 * This file is part of ShowPointAmount4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ShowPointAmount4;

use Eccube\Entity\Cart;
use Eccube\Entity\CartItem;
use Eccube\Entity\Product;
use Eccube\Entity\ProductClass;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\PluginRepository;
use Plugin\ShowPointAmount4\Repository\ConfigRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Event implements EventSubscriberInterface
{
    /**
     * @var BaseInfoRepository
     */
    private $baseInfoRepository;

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * @var PluginRepository
     */
    private $pluginRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'Product/detail.twig' => 'onProductTwig',
            'Cart/index.twig' => 'onCartIndexTwig',
        ];
    }

    public function __construct(
        BaseInfoRepository $baseInfoRepository,
        ConfigRepository $configRepository,
        PluginRepository $pluginRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->baseInfoRepository = $baseInfoRepository;
        $this->configRepository = $configRepository;
        $this->pluginRepository = $pluginRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    public function onProductTwig(TemplateEvent $event)
    {
        $BaseInfo = $this->baseInfoRepository->get();

        if (!$BaseInfo->isOptionPoint()) {
            return;
        }

        if (!$this->configRepository->get()->isOptionShowInProductDetail()) {
            return;
        }

        $basicPointRate = $BaseInfo->getBasicPointRate();

        $Product = $event->getParameter('Product');
        $json = $this->getClassCategoriesAsJson($Product);

        $points = [];
        /* @var $productClass ProductClass */
        foreach ($Product->getProductClasses() as $ProductClass) {
            if (!is_null($ProductClass->getPointRate())) {
                $rate = $ProductClass->getPointRate();
            } else {
                $rate = $basicPointRate;

                $Plugin = $this->pluginRepository->findOneBy([
                    'code' => 'UMCustomerRank4',
                    'enabled' => true,
                ]);

                if (!is_null($Plugin) && $this->authorizationChecker->isGranted('ROLE_USER')) {
                    $Customer = $this->tokenStorage->getToken()->getUser();
                    $CustomerRank = $Customer->getCustomerRank();
                    if (!is_null($CustomerRank) && !is_null($CustomerRank->getPointRate())) {
                        $rate = $Customer->getCustomerRank()->getPointRate();
                    }
                }
            }

            if ($rate !== null) {
                $points[] = round($ProductClass->getPrice02() * ($rate / 100)) * 1;
            }
        }

        $event->setParameter('point_min', min($points));
        $event->setParameter('point_max', max($points));

        $event->setParameter('point_json', $json);
        $event->addSnippet('@ShowPointAmount4/product_detail_point.twig');
        $event->addSnippet('@ShowPointAmount4/product_detail_point_js.twig');
    }

    public function onCartIndexTwig(TemplateEvent $event)
    {
        if (!$this->baseInfoRepository->get()->isOptionPoint()) {
            return;
        }

        if (!$this->configRepository->get()->isOptionShowInCart()) {
            return;
        }

        $Carts = $event->getParameter('Carts');
        if (empty($Carts)) {
            return;
        }

        foreach ($Carts as $Cart) {
            $points[] = $this->calculateAddPoint($Cart);
        }

        $event->setParameter('arrCartPoint', $points);
        $event->addSnippet('@ShowPointAmount4/cart_index.twig');
    }

    /**
     * Get the ClassCategories as JSON.
     *
     * @return string
     */
    public function getClassCategoriesAsJson(Product $Product)
    {
        $Product->_calc();
        $class_categories = [
            '__unselected' => [
                '__unselected' => [
                    'name' => trans('common.select'),
                    'product_class_id' => '',
                ],
            ],
        ];

        $BaseInfo = $this->baseInfoRepository->get();
        $basicPointRate = $BaseInfo->getBasicPointRate();

        foreach ($Product->getProductClasses() as $ProductClass) {
            /** @var ProductClass $ProductClass */
            if (!$ProductClass->isVisible()) {
                continue;
            }

            if (!is_null($ProductClass->getPointRate())) {
                $rate = $ProductClass->getPointRate();
            } else {
                $rate = $basicPointRate;
            }

            /* @var $ProductClass \Eccube\Entity\ProductClass */
            $ClassCategory1 = $ProductClass->getClassCategory1();
            $ClassCategory2 = $ProductClass->getClassCategory2();
            if ($ClassCategory2 && !$ClassCategory2->isVisible()) {
                continue;
            }
            $class_category_id1 = $ClassCategory1 ? (string) $ClassCategory1->getId() : '__unselected2';
            $class_category_id2 = $ClassCategory2 ? (string) $ClassCategory2->getId() : '';

            if ($rate === null) {
                $point = '';
            } else {
                $point = round($ProductClass->getPrice02() * ($rate / 100)) * 1;
            }

            $class_categories[$class_category_id1]['#'] = [
                'classcategory_id2' => '',
                'name' => trans('common.select'),
                'product_class_id' => '',
            ];
            $class_categories[$class_category_id1]['#'.$class_category_id2] = [
                'point' => $point,
            ];
        }

        return json_encode($class_categories);
    }

    /**
     * 付与ポイントを計算.
     *
     * @return int
     */
    private function calculateAddPoint(Cart $cart)
    {
        $BaseInfo = $this->baseInfoRepository->get();
        $basicPointRate = $BaseInfo->getBasicPointRate();

        // 明細ごとのポイントを集計
        $totalPoint = array_reduce($cart->getItems()->toArray(),
            function ($carry, CartItem $item) use ($basicPointRate) {
                $pointRate = $item->isProduct() ? $item->getProductClass()->getPointRate() : null;
                if ($pointRate === null) {
                    $pointRate = $basicPointRate;
                }

                // ポイント = 単価 * ポイント付与率 * 数量
                $point = round($item->getProductClass()->getPrice02() * ($pointRate / 100)) * $item->getQuantity();

                return $carry + $point;
            }, 0);

        return $totalPoint < 0 ? 0 : $totalPoint;
    }
}
