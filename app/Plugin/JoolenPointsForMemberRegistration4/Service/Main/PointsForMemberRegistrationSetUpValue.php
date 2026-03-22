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
use Eccube\Entity\BaseInfo;

class PointsForMemberRegistrationSetUpValue
{
    /**
     * ポイント（常時付与設定）
     *
     * @var string|null
     */
    private $joolen_always_member_registration_point;

    /**
     * ポイント（期間中のみ付与設定）
     *
     * @var string|null
     */
    private $member_registration_point;

    /**
     * 開始日（期間中のみ付与設定）
     *
     * @var DateTime|null
     *
     */
    private $apply_datetime_start;

    /**
     * 終了日（期間中のみ付与設定）
     *
     * @var DateTime|null
     */
    private $apply_datetime_end;

    /**
     * 「メールマガジン送付について」項目の「受け取る」を選択することを報酬付与の獲得条件に含める
     *
     * @var boolean
     */
    private $joolen_include_receiving_mail_magazine;

    /**
     * PointsForMemberRegistrationSetUpValue constructor.
     *
     * @param string|null $joolen_always_member_registration_point
     * @param string|null $member_registration_point
     * @param DateTime|null $apply_datetime_start
     * @param DateTime|null $apply_datetime_end
     * @param bool $joolen_include_receiving_mail_magazine
     */
    public function __construct(
        ?string $joolen_always_member_registration_point,
        ?string $member_registration_point,
        ?DateTime $apply_datetime_start,
        ?DateTime $apply_datetime_end,
        bool $joolen_include_receiving_mail_magazine
    )
    {
        $this->joolen_always_member_registration_point = $joolen_always_member_registration_point;
        $this->member_registration_point = $member_registration_point;
        $this->apply_datetime_start = $apply_datetime_start;
        $this->apply_datetime_end = $apply_datetime_end;
        $this->joolen_include_receiving_mail_magazine = $joolen_include_receiving_mail_magazine;
    }

    public static function fromBaseInfo(BaseInfo $BaseInfo): self
    {
        return new PointsForMemberRegistrationSetUpValue(
            $BaseInfo->getJoolenAlwaysMemberRegistrationPoint(),
            $BaseInfo->getMemberRegistrationPoint(),
            $BaseInfo->getApplyDatetimeStart(),
            $BaseInfo->getApplyDatetimeEnd(),
            $BaseInfo->isJoolenIncludeReceivingMailMagazine()
        );
    }

    /**
     * @return string|null
     */
    public function getJoolenAlwaysMemberRegistrationPoint(): ?string
    {
        return $this->joolen_always_member_registration_point;
    }

    /**
     * @return string|null
     */
    public function getMemberRegistrationPoint(): ?string
    {
        return $this->member_registration_point;
    }

    /**
     * @return DateTime|null
     */
    public function getApplyDatetimeStart(): ?DateTime
    {
        return $this->apply_datetime_start;
    }

    /**
     * @return DateTime|null
     */
    public function getApplyDatetimeEnd(): ?DateTime
    {
        return $this->apply_datetime_end;
    }

    /**
     * @return boolean
     */
    public function isJoolenIncludeReceivingMailMagazine(): bool
    {
        return $this->joolen_include_receiving_mail_magazine;
    }

}