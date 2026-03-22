<?php
/*
 * Plugin Name : CustomerRank
 *
 * Copyright (C) BraTech Co., Ltd. All Rights Reserved.
 * http://www.bratech.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\CustomerRank\Service\PurchaseFlow\Processor;

use Eccube\Annotation\ShoppingFlow;
use Eccube\Annotation\OrderFlow;
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\ItemInterface;
use Eccube\Entity\Customer;
use Eccube\Entity\Order;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPostValidator;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Repository\CustomerRankRepository;
use Plugin\CustomerRank\Service\CustomerRankService;

/**
 * @ShoppingFlow
 * @OrderFlow
 */
class CustomerRankPointProcessor extends ItemHolderPostValidator
{
    protected $BaseInfo;

    private $customerRankRepository;
    private $customerRankService;

    public function __construct(
            BaseInfoRepository $baseInfoRepository,
            CustomerRankRepository $customerRankRepository,
            CustomerRankService $customerRankService
            )
    {
        $this->BaseInfo = $baseInfoRepository->get();
        $this->customerRankRepository = $customerRankRepository;
        $this->customerRankService = $customerRankService;
    }

    public function validate(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        if (!$this->supports($itemHolder)) {
            return;
        }

        $Customer = $this->customerRankService->getLoginCustomer();
        if(is_null($Customer))$Customer = $itemHolder->getCustomer();
        if(!$Customer instanceof Customer)return;
        if(!is_null($Customer))$this->customerRankService->checkRank($Customer);

        // 付与ポイントを計算
        $addPoint = $this->calculateAddPoint($itemHolder);
        $itemHolder->setAddPoint($addPoint);
    }

    private function calculateAddPoint(ItemHolderInterface $itemHolder)
    {
        $basicPointRate = $this->BaseInfo->getBasicPointRate();
        $customerRankId = $itemHolder->getCustomerRankId();
        if(!is_null($customerRankId)){
            $CustomerRank = $this->customerRankRepository->find($customerRankId);
        }
        if(!isset($CustomerRank)){
            $CustomerRank = $itemHolder->getCustomer()->getCustomerRank();
        }
        $customerPointRate = 0;
        if(!is_null($CustomerRank))$customerPointRate = $CustomerRank->getPointRate();

        // 明細ごとのポイントを集計
        $totalPoint = array_reduce($itemHolder->getItems()->toArray(),
            function ($carry, ItemInterface $item) use ($basicPointRate, $customerPointRate) {
                $pointRate = $item->isProduct() ? $item->getProductClass()->getPointRate() : null;
                if ($pointRate === null) {
                    $pointRate = $basicPointRate;
                }
                $pointRate += $customerPointRate;
                if($pointRate < 0)$pointRate = 0;

                // TODO: ポイントは税抜き分しか割引されない、ポイント明細は税抜きのままでいいのか？
                $point = 0;
                if ($item->isPoint()) {
                    $point = round($item->getPrice() * ($pointRate / 100)) * $item->getQuantity();
                // Only calc point on product
                } elseif ($item->isProduct()) {
                    // ポイント = 単価 * ポイント付与率 * 数量
                    $point = round($item->getPrice() * ($pointRate / 100)) * $item->getQuantity();
                } elseif($item->isDiscount()) {
                    $point = round($item->getPrice() * ($pointRate / 100)) * $item->getQuantity();
                }

                return $carry + $point;
            }, 0);

        return $totalPoint < 0 ? 0 : $totalPoint;
    }

    private function supports(ItemHolderInterface $itemHolder)
    {
        if (!$this->BaseInfo->isOptionPoint()) {
            return false;
        }

        if (!$itemHolder instanceof Order) {
            return false;
        }

        if (!$itemHolder->getCustomer()) {
            return false;
        }

        return true;
    }
}
