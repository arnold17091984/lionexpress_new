<?php

namespace Customize\Nav;

use Eccube\Common\EccubeNav;

class FaqNav implements EccubeNav
{
    public static function getNav(): array
    {
        return [
            'content' => [
                'children' => [
                    'faq' => [
                        'name' => 'FAQ管理',
                        'url' => 'admin_content_faq',
                    ],
                ],
            ],
        ];
    }
}
