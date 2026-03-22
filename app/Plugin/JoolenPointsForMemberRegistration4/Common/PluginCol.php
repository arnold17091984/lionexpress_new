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

namespace Plugin\JoolenPointsForMemberRegistration4\Common;

class PluginCol
{
    /**
     * 店舗情報
     */
    const MEMBER_ALWAYS_REGISTRATION_POINT = 'joolen_always_member_registration_point'; // ポイント（常時付与設定）
    const MEMBER_REGISTRATION_POINT_DURING_PERIOD = 'member_registration_point';        // ポイント（期間中のみ付与設定）
    const MEMBER_REGISTRATION_POINT_PERIOD_START = 'apply_datetime_start';              // 開始日（期間中のみ付与設定）
    const MEMBER_REGISTRATION_POINT_PERIOD_END = 'apply_datetime_end';                  // 終了日（期間中のみ付与設定）
    const INCLUDE_RECEIVING_MAIL_MAGAZINE = 'joolen_include_receiving_mail_magazine';   // 「メールマガジン送付について」項目の「受け取る」を選択することを報酬付与の獲得条件に含める
}