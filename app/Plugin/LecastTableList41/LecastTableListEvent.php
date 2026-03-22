<?php
/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2018 lecast system.
  * @author Tetsuji Shiro
  *
  * このプラグインは再販売禁止です。
  */

namespace Plugin\LecastTableList41;

use Eccube\Event\TemplateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LecastTableListEvent implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            '@admin/Product/product.twig' => 'onRenderAdminProduct',
        ];
    }

    /**
     * 管理画面：商品登録画面に関連商品登録フォームを表示する.
     *
     * @param TemplateEvent $event
     */
    public function onRenderAdminProduct(TemplateEvent $event)
    {
        $event->addSnippet('@LecastTableList41/admin/admin_table_list.twig');
    }

}