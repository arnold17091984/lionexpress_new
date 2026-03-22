<?php

/*
 * This file is part of Refine
 *
 * Copyright(c) 2021 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineCheckItem\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Controller\AbstractController;
use Eccube\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RefineCheckItemController.
 */
class RefineCheckItemController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * RefineCheckItemController constructor.
     *
     * @param ProductRepository $productRepository
     * @throws \Exception
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * アクセス履歴
     *
     * @param Request $request
     *
     * @return array
     *
     * @Route("/block/refine_check_item", name="block_refine_check_item")
     * @Template("Block/refine_check_item.twig")
     */
    public function index(Request $request)
    {
        // Cookie 取得
        $productIds = (new ArrayCollection($this->getProductIdsFromCookie($request)))->toArray();
        // 表示用に順序を逆にする
        $productIds = array_reverse($productIds);

        $Products = array();
        foreach ($productIds as $productId)
        {
            /** @var \Eccube\Entity\Product $Product */
            $Product = $this->productRepository->find($productId);
            $hasStock = false;

            if ($Product)
            {
                /** @var \Eccube\Entity\ProductClass $ProductClass */
                foreach ($Product->getProductClasses() as $ProductClass)
                {
                    if ($ProductClass->isStockUnlimited() || $ProductClass->getStock() > 0)
                    {
                        $hasStock = true;
                        break;
                    }
                }
                $Products[] = [
                    'Product' => $Product,
                    'hasStock' => $hasStock,
                ];
            }
        }

        return [
            'Products' => $Products,
        ];
    }

    /**
     * Cookie の取得
     *
     * @param Request $request
     * @return array|mixed
     */
    private function getProductIdsFromCookie(Request $request)
    {
        $cookie = $request->cookies->get(\Plugin\RefineCheckItem\Event::COOKIE_NAME);

        return json_decode($cookie, true) ?? [];
    }
}
