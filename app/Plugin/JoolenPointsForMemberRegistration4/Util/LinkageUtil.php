<?php

/*
 * Plugin Name: JoolenPointsForMemberRegistration4
 *
 * Copyright(c) joolen inc. All Rights Reserved.
 *
 * https://www.joolen.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\JoolenPointsForMemberRegistration4\Util;

use Eccube\Entity\Plugin;
use Eccube\Repository\PluginRepository;

/**
 * プラグイン連携関連のユーティリティクラス.
 */
class LinkageUtil
{
    /**
     * プラグイン問い合わせ結果の連想配列
     *
     * For example
     *  * array:8 [▼
     *      "JoolenFixedPointsForProduct4" => Eccube\Entity\Plugin,
     *      "JoolenPointRateForProduct4" => null,
     *      "JoolenNoPointsForProduct4" => Eccube\Entity\Plugin
     *               〜
     *      "JoolenRankedItemBlock4" => null
     *    ]
     *
     * @var Plugin[]
     */
    private $resultPlugins;

    /**
     * @var PluginRepository
     */
    private $pluginRepository;

    /**
     * LinkageUtil constructor.
     */
    public function __construct(
        PluginRepository $pluginRepository
    )
    {
        $this->pluginRepository = $pluginRepository;

        $this->resultPlugins = array_combine(LinkablePlugin::CODE_LIST, array_map(function ($linkablePluginCode) {
            return $this->pluginRepository->findOneBy([
                'code' => $linkablePluginCode,
            ]);
        }, LinkablePlugin::CODE_LIST));
    }

    /**
     * プラグインの存在確認
     *
     * @param string $code
     * @return bool
     */
    public function pluginExists(string $code): bool
    {
        if (!array_key_exists($code, $this->resultPlugins)) {
            return false;
        }

        if (is_null($this->resultPlugins[$code])) {
            return false;
        }

        return true;
    }

    /**
     * プラグインの連携済確認
     *
     * @param string $code
     * @return bool
     */
    public function canLink(string $code): bool
    {
        if (!$this->pluginExists($code)) {
            return false;
        }

        if (!$this->resultPlugins[$code]->isInitialized()) {
            return false;
        }

        if (!$this->resultPlugins[$code]->isEnabled()) {
            return false;
        }

        return true;
    }
}
