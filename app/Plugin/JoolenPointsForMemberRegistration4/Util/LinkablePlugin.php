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

class LinkablePlugin
{
    /**
     * 本プラグインのコード定義
     */
    const SELF_CODE = 'JoolenPointsForMemberRegistration4'; // 新規会員登録時ポイント付与プラグイン

    /**
     * 連携可能なプラグインコード定義
     */
    const MAIL_MAGAZINE = 'MailMagazine4';   // メールマガジンプラグイン

    /**
     * 連携可能なプラグインコード一覧定義
     */
    const CODE_LIST = [
        self::MAIL_MAGAZINE,
    ];
}
