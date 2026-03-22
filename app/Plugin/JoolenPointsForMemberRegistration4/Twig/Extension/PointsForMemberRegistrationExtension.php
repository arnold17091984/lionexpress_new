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

namespace Plugin\JoolenPointsForMemberRegistration4\Twig\Extension;

use Eccube\Entity\BaseInfo;
use Eccube\Repository\BaseInfoRepository;
use Exception;
use Plugin\JoolenPointsForMemberRegistration4\Service\Main\PointsForMemberRegistrationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PointsForMemberRegistrationExtension extends AbstractExtension
{
    /**
     * @var BaseInfo
     */
    private $baseInfo;

    /**
     * @var PointsForMemberRegistrationService
     */
    private $pointsForMemberRegistrationService;

    /**
     * PointsForMemberRegistrationExtension constructor.
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
     * Returns a list of functions to add to the existing list.
     *
     * @return TwigFunction[] An array of functions
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'get_always_point_plg_joolen_points_for_member_registration4',
                [$this, 'getAlwaysPoint']
            ),
            new TwigFunction(
                'get_during_period_point_plg_joolen_points_for_member_registration4',
                [$this, 'getDuringPeriodPoint']
            ),
            new TwigFunction(
                'get_start_date_plg_joolen_points_for_member_registration4',
                [$this, 'getStartDate']
            ),
            new TwigFunction(
                'get_end_date_plg_joolen_points_for_member_registration4',
                [$this, 'getEndDate']
            ),
            new TwigFunction(
                'is_during_period_plg_joolen_points_for_member_registration4',
                [$this, 'isDuringPeriod']
            ),
        ];
    }

    /**
     * 「ポイント（常時付与設定）」を取得する
     *
     * @return string|null
     */
    public function getAlwaysPoint(): ?string
    {
        // ポイント（常時付与設定）」を取得する
        $point = $this->baseInfo->getJoolenAlwaysMemberRegistrationPoint();

        // ショップが、設定完了していない場合はnullを返却する
        if (!$this->pointsForMemberRegistrationService->isShopSetUpComplete($point)) {
            return null;
        }

        return $point;
    }

    /**
     * 「ポイント（期間中のみ付与設定）」を取得する
     *
     * @return string|null
     */
    public function getDuringPeriodPoint(): ?string
    {
        // 「ポイント（期間中のみ付与設定）」を取得する
        $point = $this->baseInfo->getMemberRegistrationPoint();

        // ショップが、設定完了していない場合はnullを返却する
        if (!$this->pointsForMemberRegistrationService->isShopSetUpComplete($point)) {
            return null;
        }

        return $point;
    }

    /**
     * 「開始日（期間中のみ付与設定）」を取得する
     *
     * @param string|null $dateFormat
     * @return string|null
     */
    public function getStartDate(string $dateFormat): ?string
    {
        // 「開始日（期間中のみ付与設定）」を取得する
        $startDate = $this->baseInfo->getApplyDatetimeStart();

        return $startDate->format($dateFormat);
    }

    /**
     * 「終了日（期間中のみ付与設定）」を取得する
     *
     * @param string|null $dateFormat
     * @return string|null
     */
    public function getEndDate(string $dateFormat): ?string
    {
        // 「終了日（期間中のみ付与設定）」を取得する
        $endDate = $this->baseInfo->getApplyDatetimeEnd();

        return $endDate->format($dateFormat);
    }

    /**
     * ショップがキャンペーン開催中か判定
     *
     * @return bool
     */
    public function isDuringPeriod(): bool
    {
        // 「ポイント（期間中のみ付与設定）」を取得する
        $point = $this->baseInfo->getMemberRegistrationPoint();

        // ショップが、設定完了していない場合は処理終了
        if (!$this->pointsForMemberRegistrationService->isShopSetUpComplete($point)) {
            return false;
        }

        // ショップが、キャンペーン開催中ではない場合は処理終了
        $startDate = $this->baseInfo->getApplyDatetimeStart();
        $endDate = $this->baseInfo->getApplyDatetimeEnd();
        if (!$this->pointsForMemberRegistrationService->isShopCampaignUnderway($startDate, $endDate)) {
            return false;
        }

        return true;
    }

}