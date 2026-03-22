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

namespace Plugin\JoolenPointsForMemberRegistration4\Service\Main;

class PointsForMemberRegistrationApiResult
{
    /**
     * ショップ側の設定は完了しているか
     * @var bool
     */
    private $shopSetUpComplete;

    /**
     * ショップがキャンペーン開催中か
     * @var bool
     */
    private $shopCampaignUnderway;

    /**
     * 設定値の配列
     * @var PointsForMemberRegistrationSetUpValue[]
     */
    private $setUpValue;

    /**
     * PointsForMemberRegistrationApiResult constructor.
     *
     * @param bool $shopSetUpComplete
     * @param bool $shopCampaignUnderway
     * @param PointsForMemberRegistrationSetUpValue $setUpValue
     */
    public function __construct(
        bool $shopSetUpComplete,
        bool $shopCampaignUnderway,
        PointsForMemberRegistrationSetUpValue $setUpValue
    )
    {
        $this->shopSetUpComplete = $shopSetUpComplete;
        $this->shopCampaignUnderway = $shopCampaignUnderway;
        $this->setUpValue = $setUpValue;
    }

    /**
     * ショップの設定は完了しているか判定
     *
     * @return bool
     */
    public function isShopSetUpComplete(): bool
    {
        return $this->shopSetUpComplete;
    }

    /**
     * ショップがキャンペーン開催中か判定
     *
     * @return bool
     */
    public function isShopCampaignUnderway(): bool
    {
        return $this->shopCampaignUnderway;
    }

    /**
     * 設定値の配列を取得
     *
     * @return PointsForMemberRegistrationSetUpValue
     */
    public function getSetUpValue(): PointsForMemberRegistrationSetUpValue
    {
        return $this->setUpValue;
    }

}