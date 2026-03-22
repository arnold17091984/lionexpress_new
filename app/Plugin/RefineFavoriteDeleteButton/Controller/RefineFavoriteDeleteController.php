<?php

/*
 * This file is part of Refine
 *
 * Copyright(c) 2024 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineFavoriteDeleteButton\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Eccube\Repository\ProductRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RefineFavoriteController.
 */
class RefineFavoriteDeleteController extends AbstractController
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CustomerFavoriteProductRepository
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     */
    protected $customerFavoriteProductRepository;

    /**
     * RefineFavoriteDeleteController constructor.
     *
     * @param ProductRepository $productRepository
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        CustomerFavoriteProductRepository $customerFavoriteProductRepository
    )
    {
        $this->productRepository = $productRepository;
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
    }

    /**
     * お気に入り削除
     *
     * @Route("/refine_delete_favorite", name="refine_delete_favorite")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFavorite(Request $request)
    {
        /** @var \Eccube\Entity\Product $Product */
        $Product = $this->productRepository->find($request->get('id'));

        if ($Product && $this->isGranted('ROLE_USER'))
        {
            /** @var \Eccube\Entity\Customer $Customer */
            $Customer = $this->getUser();

            /** @var \Eccube\Entity\CustomerFavoriteProduct $CustomerFavoriteProduct */
            $CustomerFavoriteProduct = $this->customerFavoriteProductRepository->findOneBy(['Customer' => $Customer, 'Product' => $Product]);

            if ($CustomerFavoriteProduct)
            {
                $this->customerFavoriteProductRepository->delete($CustomerFavoriteProduct);
                $this->session->getFlashBag()->set('refine_favorite_delete_button.deleted', $Product->getId());
            }
            else
            {
                $this->session->getFlashBag()->set('refine_favorite_delete_button.already_deleted', $Product->getId());
            }
        }
        else
        {
            $this->session->getFlashBag()->set('refine_favorite_delete_button.none_product', $Product->getId());
        }

        return $this->redirectToRoute('product_detail', ['id' => $request->get('id')]);
    }
}
