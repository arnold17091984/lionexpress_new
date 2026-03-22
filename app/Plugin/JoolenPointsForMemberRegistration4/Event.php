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

namespace Plugin\JoolenPointsForMemberRegistration4;

use Eccube\Entity\BaseInfo;
use Eccube\Entity\Customer;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Event\TemplateEvent;
use Eccube\Repository\BaseInfoRepository;
use Exception;
use Plugin\JoolenPointsForMemberRegistration4\Service\Main\PointsForMemberRegistrationService;
use Plugin\JoolenPointsForMemberRegistration4\Service\Main\PointsForMemberRegistrationSetUpValue;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Event implements EventSubscriberInterface
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
     * Event constructor.
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
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // --------------
            // Admin
            // --------------
            '@admin/Setting/Shop/shop_master.twig' => 'onRenderShopMaster',

            //---------------
            // Front
            // --------------
            EccubeEvents::FRONT_ENTRY_ACTIVATE_COMPLETE => 'onEntryActivateComplete',
        ];
    }

    /**
     * [管理]設定＞店舗設定＞基本設定画面の表示
     */
    public function onRenderShopMaster(TemplateEvent $event)
    {
        $twig = '@JoolenPointsForMemberRegistration4/admin/Setting/Shop/shop_master.twig';
        $event->addSnippet($twig);
    }

    /**
     * [フロント]メール認証時
     * 仮会員機能が無効の場合、EntryController内でentryActivateの処理を実行するため「FRONT_ENTRY_ACTIVATE_COMPLETE」イベントに該当する
     *       〃 が有効な場合、メールにて送信されたURLにアクセスすることで「FRONT_ENTRY_ACTIVATE_COMPLETE」イベントが実行される
     */
    public function onEntryActivateComplete(EventArgs $EventArgs)
    {
        /** @var Customer $Customer */
        $Customer = $EventArgs->getArgument('Customer');

        // 店舗情報から設定値を取得する
        $setUpValue = PointsForMemberRegistrationSetUpValue::fromBaseInfo($this->baseInfo);

        // 必要に応じて「ポイント（常時付与設定）」を付与する
        $this->pointsForMemberRegistrationService->grantAlwaysPointIfNeeded($setUpValue, $Customer);

        // 必要に応じて「ポイント（期間中のみ付与設定）」を付与する
        $this->pointsForMemberRegistrationService->grantPointDuringPeriodIfNeeded($setUpValue, $Customer);
    }
}
