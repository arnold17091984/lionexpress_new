<?php

namespace Plugin\SEOAllOne;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {
        $result = [
            'SEOAllOne' => [
                'name'  => 'SEO All One',
                'icon'  => 'fa-file-text-o',
                'children' => [
                    'seoallone_admin_sitemap_config' => [
                        'name' => 'Sitemap管理',
                        'url' => 'seoallone_admin_sitemap_config',
                    ],
                    'seo_all_one_admin_config'    => [
                        'name'  => 'メイン設定',
                        'url'   => 'seo_all_one_admin_config'
                    ],
                    'seoallone_admin_manual' => [
                        'name'  => 'マニュアル',
                        'url'   => 'seoallone_admin_manual'
                    ]
                ],
            ],
        ];
        return $result;
    }
}
