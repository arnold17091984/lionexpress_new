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

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Repository\BaseInfoRepository;
use Exception;
use Plugin\JoolenPointsForMemberRegistration4\Entity\PointAddHistoryForMemberRegistration;
use Plugin\JoolenPointsForMemberRegistration4\Repository\PointAddHistoryForMemberRegistrationRepository;
use Plugin\JoolenPointsForMemberRegistration4\Util\LinkablePlugin;
use Plugin\JoolenPointsForMemberRegistration4\Util\LinkageUtil;

class PointsForMemberRegistrationService
{
    /**
     * @var BaseInfo
     */
    private $baseInfo;

    /**
     * @var PointAddHistoryForMemberRegistrationRepository
     */
    private $pointAddHistoryForMemberRegistrationRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var LinkageUtil
     */
    private $linkageUtil;

    /**
     * PointsForMemberRegistrationService constructor.
     *
     * @param BaseInfoRepository $baseInfoRepository
     * @param PointAddHistoryForMemberRegistrationRepository $pointAddHistoryForMemberRegistrationRepository
     * @param EntityManagerInterface $entityManager
     * @param LinkageUtil $linkageUtil
     * @throws Exception
     */
    public function __construct(
        BaseInfoRepository $baseInfoRepository,
        PointAddHistoryForMemberRegistrationRepository $pointAddHistoryForMemberRegistrationRepository,
        EntityManagerInterface $entityManager,
        LinkageUtil $linkageUtil
    )
    {
        $this->baseInfo = $baseInfoRepository->get();
        $this->pointAddHistoryForMemberRegistrationRepository = $pointAddHistoryForMemberRegistrationRepository;
        $this->entityManager = $entityManager;
        $this->linkageUtil = $linkageUtil;
    }

    /**
     * 必要に応じて「ポイント（常時付与設定）」を付与する
     *
     * @param PointsForMemberRegistrationSetUpValue $setUpValue
     * @param Customer $Customer
     */
    public function grantAlwaysPointIfNeeded(PointsForMemberRegistrationSetUpValue $setUpValue, Customer $Customer): void
    {
        // 「ポイント（常時付与設定）」を取得する
        $point = $setUpValue->getJoolenAlwaysMemberRegistrationPoint();

        // ショップが、設定完了していない場合は処理終了
        if (!$this->isShopSetUpComplete($point)) {
            return;
        }

        // 会員が、ショップ側の定めた条件を満たしていない場合は処理終了
        $includeReceivingMailMagazine = $setUpValue->isJoolenIncludeReceivingMailMagazine();
        if (!$this->hasCustomerAchievedConditions($includeReceivingMailMagazine, $Customer)) {
            return;
        }

        // 既にポイント付与履歴がある場合は処理終了
        if ($this->isExistPointAddHistory(
            $Customer,
            PointAddHistoryForMemberRegistration::TYPE_NORMAL
        )) {
            return;
        }

        // 会員にポイントを付与する
        $this->grantPointToCustomer(
            $point,
            $Customer,
            PointAddHistoryForMemberRegistration::TYPE_NORMAL
        );
    }

    /**
     * 必要に応じて「ポイント（期間中のみ付与設定）」を付与する
     *
     * @param PointsForMemberRegistrationSetUpValue $setUpValue
     * @param Customer $Customer
     */
    public function grantPointDuringPeriodIfNeeded(PointsForMemberRegistrationSetUpValue $setUpValue, Customer $Customer): void
    {
        // 「ポイント（期間中のみ付与設定）」を取得する
        $point = $setUpValue->getMemberRegistrationPoint();

        // ショップが、設定完了していない場合は処理終了
        if (!$this->isShopSetUpComplete($point)) {
            return;
        }

        // ショップが、キャンペーン開催中ではない場合は処理終了
        $startDate = $setUpValue->getApplyDatetimeStart();
        $endDate = $setUpValue->getApplyDatetimeEnd();
        if (!$this->isShopCampaignUnderway($startDate, $endDate)) {
            return;
        }

        // 会員が、ショップ側の定めた条件を満たしていない場合は処理終了
        $includeReceivingMailMagazine = $setUpValue->isJoolenIncludeReceivingMailMagazine();
        if (!$this->hasCustomerAchievedConditions($includeReceivingMailMagazine, $Customer)) {
            return;
        }

        // 既にポイント付与履歴がある場合は処理終了
        if ($this->isExistPointAddHistory(
            $Customer,
            PointAddHistoryForMemberRegistration::TYPE_PERIOD_LIMITED
        )) {
            return;
        }

        // 会員にポイントを付与する
        $this->grantPointToCustomer(
            $point,
            $Customer,
            PointAddHistoryForMemberRegistration::TYPE_PERIOD_LIMITED
        );
    }

    /**
     * ポイント付与履歴があるかどうか
     *
     * @param Customer $Customer
     * @param int $type
     * @return bool
     */
    private function isExistPointAddHistory(Customer $Customer, int $type)
    {
        $history = $this->pointAddHistoryForMemberRegistrationRepository
            ->findOneBy([
                'Customer' => $Customer,
                'email' => $Customer->getEmail(),
                'type' => $type,
            ]);
        return !is_null($history);
    }

    /**
     * ショップの設定は完了しているか判定
     *
     * @param string|null $pointString
     * @return bool
     */
    public function isShopSetUpComplete(?string $pointString): bool
    {
        // 「ポイント機能」自体が無効な場合はfalseを返却
        if (!$this->baseInfo->isOptionPoint()) {
            return false;
        }

        // 未登録の場合はfalseを返却
        if (is_null($pointString)) {
            return false;
        }

        // 0を設定している場合はfalseを返却
        $point = intval($pointString);
        if ($point === 0) {
            return false;
        }

        return true;
    }

    /**
     * ショップがキャンペーン開催中か判定
     *
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return bool
     */
    public function isShopCampaignUnderway(?DateTime $startDate, ?DateTime $endDate): bool
    {
        // 付与期間外の場合はfalseを返却
        $TimeService = \Plugin\JoolenPointsForMemberRegistration4\Service\TimeService::getInstance();
        return $TimeService->isInTerm($startDate, $endDate);
    }

    /**
     * 会員がショップ側の定めた報酬獲得条件を満たしているか判定
     *
     * @param bool $includeReceivingMailMagazine
     * @param Customer $Customer
     * @return bool
     */
    private function hasCustomerAchievedConditions(bool $includeReceivingMailMagazine, Customer $Customer): bool
    {
        // 「メールマガジンプラグインと連携している」 かつ、
        // 「メールマガジン送付について」項目の「受け取る」を選択することを報酬付与の条件に含める場合
        if ($this->linkageUtil->canLink(LinkablePlugin::MAIL_MAGAZINE) && $includeReceivingMailMagazine) {

            // 会員登録時に会員が「メールマガジン送付について」項目を「受け取らない」を選択した場合はfalseを返却
            // MEMO メルマガ管理プラグイン側の変更に影響を受ける
            // 　　　EC-CUBEバージョンによっては文字列の場合もあるため型チェックまでは行わない
            if ($Customer->getMailmagaFlg() == 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * 会員にポイントを付与する
     *
     * @param string|null $pointString
     * @param Customer $Customer
     * @param int $tpye
     */
    private function grantPointToCustomer(string $pointString, Customer $Customer, int $type): void
    {
        // 会員の現在の保有ポイントに加算する
        $addPoint = intval($Customer->getPoint()) + intval($pointString);

        // ポイントをセット
        $Customer->setPoint($addPoint);
        $PointAddHistoryForMemberRegistration = (new PointAddHistoryForMemberRegistration())
            ->setCustomer($Customer)
            ->setEmail($Customer->getEmail())
            ->setType($type);
        $this->entityManager->persist($PointAddHistoryForMemberRegistration);
        $this->entityManager->flush();
    }

}