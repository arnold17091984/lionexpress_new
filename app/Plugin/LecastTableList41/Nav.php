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

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getNav()
    {
        return [
            'content' => [
                'children' => [
                    'plugin_table_list' => [
                        'name' => '表管理',
                        'url' => 'admin_table_list',
                    ],
                ],
            ],
        ];
    }
}
