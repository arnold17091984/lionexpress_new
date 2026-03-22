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

namespace Plugin\JoolenPointsForMemberRegistration4\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation\EntityExtension;

/**
 * @EntityExtension("Eccube\Entity\BaseInfo")
 */
trait BaseInfoTrait
{
    /**
     * ポイント（常時付与設定）
     *
     * @var string|null
     * @ORM\Column(name="joolen_always_member_registration_point", type="decimal", precision=10, scale=0, options={"unsigned":true}, nullable=true)
     */
    private $joolen_always_member_registration_point;

    /**
     * ポイント（期間中のみ付与設定）
     *
     * @var string|null
     * @ORM\Column(name="member_registration_point", type="decimal", precision=10, scale=0, options={"unsigned":true}, nullable=true)
     */
    private $member_registration_point;

    /**
     * 開始日（期間中のみ付与設定）
     *
     * @var DateTime|null
     *
     * @ORM\Column(name="apply_datetime_start", type="datetimetz", nullable=true)
     */
    private $apply_datetime_start;

    /**
     * 終了日（期間中のみ付与設定）
     *
     * @var DateTime|null
     *
     * @ORM\Column(name="apply_datetime_end", type="datetimetz", nullable=true)
     */
    private $apply_datetime_end;

    /**
     * 「メールマガジン送付について」項目の「受け取る」を選択することを報酬付与の獲得条件に含める
     *
     * @var boolean
     *
     * @ORM\Column(name="joolen_include_receiving_mail_magazine", type="boolean", options={"default":false})
     */
    private $joolen_include_receiving_mail_magazine = false;

    /**
     * @param string|null $joolen_always_member_registration_point
     * @return $this
     */
    public function setJoolenAlwaysMemberRegistrationPoint(?string $joolen_always_member_registration_point): self
    {
        $this->joolen_always_member_registration_point = $joolen_always_member_registration_point;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getJoolenAlwaysMemberRegistrationPoint(): ?string
    {
        return $this->joolen_always_member_registration_point;
    }

    /**
     * @param string|null $member_registration_point
     * @return $this
     */
    public function setMemberRegistrationPoint(?string $member_registration_point): self
    {
        $this->member_registration_point = $member_registration_point;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMemberRegistrationPoint(): ?string
    {
        return $this->member_registration_point;
    }

    /**
     * @param DateTime|null $apply_datetime_start
     * @return $this
     */
    public function setApplyDatetimeStart(?DateTime $apply_datetime_start = null): self
    {
        $this->apply_datetime_start = $apply_datetime_start;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getApplyDatetimeStart(): ?DateTime
    {
        return $this->apply_datetime_start;
    }

    /**
     * @param DateTime|null $apply_datetime_end
     * @return $this
     */
    public function setApplyDatetimeEnd(?DateTime $apply_datetime_end = null): self
    {
        $this->apply_datetime_end = $apply_datetime_end;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getApplyDatetimeEnd(): ?DateTime
    {
        return $this->apply_datetime_end;
    }

    /**
     * @param boolean $joolen_include_receiving_mail_magazine
     * @return $this
     */
    public function setJoolenIncludeReceivingMailMagazine(bool $joolen_include_receiving_mail_magazine): self
    {
        $this->joolen_include_receiving_mail_magazine = $joolen_include_receiving_mail_magazine;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isJoolenIncludeReceivingMailMagazine(): bool
    {
        return $this->joolen_include_receiving_mail_magazine;
    }

}
