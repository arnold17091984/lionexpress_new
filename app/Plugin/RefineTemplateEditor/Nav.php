<?php
/*
 * This file is part of Refine
 *
 * Copyright(c) 2022 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineTemplateEditor;

use Eccube\Common\EccubeNav;

class Nav implements EccubeNav
{
    /**
     * @return array
     */
    public static function getNav()
    {

        return [

            // コンテンツ管理に追加
            'content' => [
                'children' => [
                    'refine_template_editor' => [
                        'name' => "admin.menu.content.template_editor",
                        'url' => 'refine_template_editor_content_template_editor_index'
                    ]
                ]
            ]

        ];
    }
}
