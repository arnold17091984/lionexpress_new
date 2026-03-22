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
use Eccube\Entity\ItemHolderInterface;
use Eccube\Entity\Order;
use Eccube\Entity\Customer;
use Eccube\Repository\BaseInfoRepository;
use Eccube\Repository\DeliveryFeeRepository;
use Eccube\Service\PurchaseFlow\ItemHolderPreprocessor;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Service\CustomerRankService;
use Plugin\CustomerRank\Entity\CustomerRankConfig;

/**
 * @ShoppingFlow
 */
class DeliveryFeeFreeForCustomerRankByShippingPreprocessor implements ItemHolderPreprocessor
{
    private $BaseInfo;
    private $deliveryFeeRepository;
    private $customerRankService;

    public function __construct(
            BaseInfoRepository $baseInfoRepository,
            DeliveryFeeRepository $deliveryFeeRepository,
            CustomerRankService $customerRankService
            )
    {
        $this->BaseInfo = $baseInfoRepository->get();
        $this->deliveryFeeRepository = $deliveryFeeRepository;
        $this->customerRankService = $customerRankService;
    }

    public function process(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        $Customer = $this->customerRankService->getLoginCustomer();
        if(!is_null($Customer)){
            $this->customerRankService->checkRank($Customer);
            if ($itemHolder instanceof Order) {
                $Order = $itemHolder;
                if($Customer instanceof Customer){
                    $CustomerRank = $Customer->getCustomerRank();
                    if(!is_null($CustomerRank)){
                        if (strlen($CustomerRank->getDeliveryFreeCondition()) > 0) {
                            $total = 0;
                            $deliv_free = $this->customerRankService->getConfig('deliv_free');
                            foreach ($Order->getShippings() as $Shipping) {
                                $shipping_total = 0;
                                foreach ($Shipping->getProductOrderItems() as $Item) {
                                    $shipping_total += $Item->getPriceIncTax() * $Item->getQuantity();
                                }
                                if ($deliv_free == CustomerRankConfig::ENABLED) {
                                    if ($CustomerRank->getDeliveryFreeCondition() <= $shipping_total) {
                                        foreach ($Shipping->getOrderItems() as $Item) {
                                            if ($Item->isDeliveryFee()) {
                                                $Item->setQuantity(0);
                                            }
                                        }
                                    }else{
                                        $deliveryFeeProduct = 0;
                                        if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                                            /** @var OrderItem $item */
                                            foreach ($Shipping->getOrderItems() as $item) {
                                                if (!$item->isProduct()) {
                                                    continue;
                                                }
                                                $deliveryFeeProduct += $item->getProductClass()->getDeliveryFee() * $item->getQuantity();
                                            }
                                        }
                                        $Delivery = $Shipping->getDelivery();
                                        $Pref = $Shipping->getPref();
                                        foreach ($Shipping->getOrderItems() as $Item) {
                                            if ($Item->isDeliveryFee()) {
                                                $DeliveryFee = $this->deliveryFeeRepository->findOneBy([
                                                    'Delivery' => $Delivery,
                                                    'Pref' => $Pref,
                                                ]);
                                                if($DeliveryFee){
                                                    $Item->setPrice($DeliveryFee->getFee() + $deliveryFeeProduct);
                                                    $Item->setQuantity(1);
                                                }
                                            }
                                        }
                                    }
                                }
                                $total += $shipping_total;
                            }
                            if ($deliv_free != CustomerRankConfig::ENABLED) {
                                if ($CustomerRank->getDeliveryFreeCondition() <= $total) {
                                    foreach ($Order->getShippings() as $Shipping) {
                                        foreach ($Shipping->getOrderItems() as $Item) {
                                            if ($Item->isDeliveryFee()) {
                                                $Item->setQuantity(0);
                                            }
                                        }
                                    }
                                }else{
                                    foreach ($Order->getShippings() as $Shipping) {
                                        $deliveryFeeProduct = 0;
                                        if ($this->BaseInfo->isOptionProductDeliveryFee()) {
                                            /** @var OrderItem $item */
                                            foreach ($Shipping->getOrderItems() as $item) {
                                                if (!$item->isProduct()) {
                                                    continue;
                                                }
                                                $deliveryFeeProduct += $item->getProductClass()->getDeliveryFee() * $item->getQuantity();
                                            }
                                        }
                                        $Delivery = $Shipping->getDelivery();
                                        $Pref = $Shipping->getPref();
                                        foreach ($Shipping->getOrderItems() as $Item) {
                                            if ($Item->isDeliveryFee()) {
                                                $DeliveryFee = $this->deliveryFeeRepository->findOneBy([
                                                    'Delivery' => $Delivery,
                                                    'Pref' => $Pref,
                                                ]);
                                                if($DeliveryFee){
                                                    $Item->setPrice($DeliveryFee->getFee() + $deliveryFeeProduct);
                                                    $Item->setQuantity(1);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
