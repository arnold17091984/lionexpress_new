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
use Eccube\Entity\Order;
use Eccube\Service\PurchaseFlow\ItemHolderPostValidator;
use Eccube\Service\PurchaseFlow\PurchaseContext;
use Plugin\CustomerRank\Service\CustomerRankService;

/**
 * @ShoppingFlow
 * @OrderFlow
 */
class CustomerRankSaveProcessor extends ItemHolderPostValidator
{

    private $customerRankService;

    public function __construct(
            CustomerRankService $customerRankService
            )
    {
        $this->customerRankService = $customerRankService;
    }

    public function validate(ItemHolderInterface $itemHolder, PurchaseContext $context)
    {
        if (!$this->supports($itemHolder)) {
            return;
        }

        $CustomerRank = $this->customerRankService->getCustomerRank();

        if(!is_null($CustomerRank)){
            $itemHolder->setCustomerRankId($CustomerRank->getId());
            $itemHolder->setCustomerRankName($CustomerRank->getName());
        }
    }

    private function supports(ItemHolderInterface $itemHolder)
    {
        if (!$itemHolder instanceof Order) {
            return false;
        }

        if (!$itemHolder->getCustomer()) {
            return false;
        }

        return true;
    }
}
