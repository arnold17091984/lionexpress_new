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

namespace Plugin\JoolenPointsForMemberRegistration4\Form\Extension;

use Eccube\Form\Type\Admin\ShopMasterType;
use Eccube\Form\Type\ToggleSwitchType;
use Plugin\JoolenPointsForMemberRegistration4\Common\PluginCol;
use Plugin\JoolenPointsForMemberRegistration4\Form\Type\Admin\PointsForMemberRegistrationType;
use Plugin\JoolenPointsForMemberRegistration4\Form\Type\Admin\RangeDateTimeType;
use Plugin\JoolenPointsForMemberRegistration4\Util\LinkablePlugin;
use Plugin\JoolenPointsForMemberRegistration4\Util\LinkageUtil;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class ShopMasterTypeExtension extends AbstractTypeExtension
{
    /**
     * @var LinkageUtil
     */
    private $linkageUtil;

    /**
     * ShopMasterTypeExtension constructor.
     *
     * @param LinkageUtil $linkageUtil
     */
    public function __construct(
        LinkageUtil $linkageUtil
    )
    {
        $this->linkageUtil = $linkageUtil;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ポイント（常時付与設定）
            ->add(PluginCol::MEMBER_ALWAYS_REGISTRATION_POINT, PointsForMemberRegistrationType::class)
            // ポイント（期間中のみ付与設定）
            ->add(PluginCol::MEMBER_REGISTRATION_POINT_DURING_PERIOD, PointsForMemberRegistrationType::class)
            // 開始日〜終了日（期間中のみ付与設定）
            ->add('joolen_points_for_member_registration4_range_datetime', RangeDateTimeType::class, [
                RangeDateTimeType::START_NAME => PluginCol::MEMBER_REGISTRATION_POINT_PERIOD_START,
                RangeDateTimeType::END_NAME => PluginCol::MEMBER_REGISTRATION_POINT_PERIOD_END,
            ]);

        // メルマガプラグインが有効な場合にフォームを生成する
        if ($this->linkageUtil->canLink(LinkablePlugin::MAIL_MAGAZINE)) {
            $builder
                // 「メールマガジン送付について」項目の「受け取る」を選択することを報酬付与の獲得条件に含める
                ->add(PluginCol::INCLUDE_RECEIVING_MAIL_MAGAZINE, ToggleSwitchType::class);
        }
    }


    /**
     * 4.0用
     *
     * @return string
     */
    public function getExtendedType(): string
    {
        return ShopMasterType::class;
    }

    /**
     * 4.1用
     *
     * @return iterable
     */
    public static function getExtendedTypes(): iterable
    {
        yield ShopMasterType::class;
    }

}
