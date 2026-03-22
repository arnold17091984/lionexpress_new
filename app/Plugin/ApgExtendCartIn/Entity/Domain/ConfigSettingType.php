<?php
/**
 * Created by PhpStorm.
 * User: k_akiyoshi
 * Date: 2021/04/05
 * Time: 11:23
 */

namespace Plugin\ApgExtendCartIn\Entity\Domain;


class ConfigSettingType
{

    const ALL = 1;
    const INDIVIDUAL = 2;

    const LABELS = [
        self::ALL => "全商品に適応",
        self::INDIVIDUAL => "商品個別に設定",
    ];

}