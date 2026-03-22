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

use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Exception;

class PointsForMemberRegistrationApi
{
    /**
     * @var BaseInfo
     */
    private $baseInfo;

    /**
     * @var PointsForMemberRegistrationService
     */
    protected $pointsForMemberRegistrationService;

    /**
     * PointsForMemberRegistrationApi constructor.
     *
     * @param BaseInfoRepository $baseInfoRepository
     * @param PointsForMemberRegistrationService $pointsForMemberRegistrationService
     * @throws Exception
     */
    public function __construct(
        BaseInfoRepository $baseInfoRepository,
        PointsForMemberRegistrationService $pointsForMemberRegistrationService
    )
    {
        $this->baseInfo = $baseInfoRepository->get();
        $this->pointsForMemberRegistrationService = $pointsForMemberRegistrationService;
    }

    /**
     * APIから情報を取得する
     *
     * @return PointsForMemberRegistrationApiResult
     */
    public function getResult(): PointsForMemberRegistrationApiResult
    {
        // 店舗情報から設定値を取得する
        $setUpValue = PointsForMemberRegistrationSetUpValue::fromBaseInfo($this->baseInfo);

        // ショップの設定は完了しているか判定した真偽値を取得する
        $shopSetUpComplete = $this->pointsForMemberRegistrationService->isShopSetUpComplete(
            $setUpValue->getMemberRegistrationPoint()
        );

        // ショップがキャンペーン開催中か判定した真偽値を取得する
        $shopCampaignUnderway = $this->pointsForMemberRegistrationService->isShopCampaignUnderway(
            $setUpValue->getApplyDatetimeStart(),
            $setUpValue->getApplyDatetimeEnd()
        );

        return new PointsForMemberRegistrationApiResult(
            $shopSetUpComplete,
            $shopCampaignUnderway,
            $setUpValue
        );
    }

}