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

namespace Plugin\RefineFavoriteBlock\Controller;

use Eccube\Controller\AbstractController;
use Eccube\Entity\BaseInfo;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\CustomerFavoriteProductRepository;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use Plugin\RefineFavoriteBlock\Repository\RefineFavoriteBlockConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RefineFavoriteController.
 */
class RefineFavoriteController extends AbstractController
{
    /**
     * @var BaseInfo
     */
    protected $BaseInfo;

    /**
     * @var CustomerFavoriteProductRepository
     */
    private $customerFavoriteProductRepository;

    /**
     * @var RefineFavoriteBlockConfigRepository
     */
    private $refineFavoriteBlockConfigRepository;

    /**
     * RefineFavoriteController constructor.
     *
     * @param CustomerFavoriteProductRepository $customerFavoriteProductRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param RefineFavoriteBlockConfigRepository $refineFavoriteBlockConfigRepository
     * @throws \Exception
     */
    public function __construct(
        CustomerFavoriteProductRepository $customerFavoriteProductRepository,
        BaseInfoRepository $baseInfoRepository,
        RefineFavoriteBlockConfigRepository $refineFavoriteBlockConfigRepository
    )
    {
        $this->customerFavoriteProductRepository = $customerFavoriteProductRepository;
        $this->BaseInfo = $baseInfoRepository->get();
        $this->refineFavoriteBlockConfigRepository = $refineFavoriteBlockConfigRepository;
    }

    /**
     * お気に入り一覧
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     *
     * @return array
     *
     * @Route("/block/refine_favorite", name="block_refine_favorite")
     * @Template("Block/refine_favorite.twig")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        if (!$this->BaseInfo->isOptionFavoriteProduct())
        {
            return [
                'pagination' => null,
            ];
        }

        $Customer = $this->getUser();
        if (is_null($Customer))
        {
            return [
                'pagination' => null,
            ];
        }

        // ブロック設定を取得
        $Config = $this->refineFavoriteBlockConfigRepository->get();
        if (is_null($Config))
        {
            return [
                'pagination' => null,
            ];
        }

        // paginator
        $qb = $this->customerFavoriteProductRepository->getQueryBuilderByCustomer($Customer);

        $event = new EventArgs(
            [
                'qb' => $qb,
                'Customer' => $Customer,
            ],
            $request
        );
        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_MYPAGE_MYPAGE_FAVORITE_SEARCH, $event);

        $pagination = $paginator->paginate(
            $qb,
            1, // page no は固定で 1
            $Config->getDisplayNum(),
            ['wrap-queries' => true]
        );

        return [
            'BaseInfo' => $this->BaseInfo,
            'pagination' => $pagination,
            'count' => count($pagination->getItems()),
            'config' => $Config,
        ];
    }
}
