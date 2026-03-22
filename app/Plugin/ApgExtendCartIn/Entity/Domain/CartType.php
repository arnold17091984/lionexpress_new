<?php
/**
 * Created by PhpStorm.
 * User: k_akiyoshi
 * Date: 2021/04/05
 * Time: 11:23
 */

namespace Plugin\ApgExtendCartIn\Entity\Domain;


class CartType
{

    const STANDARD = 1;
    const LIST = 2;
    const GRID = 3;

    const LABELS = [
        self::STANDARD => "セレクトボックス(EC-CUBE標準)",
        self::LIST => "リスト表示",
        self::GRID => "グリッド表示",
    ];

}