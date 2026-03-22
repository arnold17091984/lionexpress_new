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

namespace Plugin\JoolenPointsForMemberRegistration4\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

/**
 * @Annotation
 */
class TwoFormRanges extends Constraint
{
    public $dateTimeMessage = '開始日より未来の値を入力してください';
    public $numberMessage = '範囲開始より大きい値を入力してください';
    public $type;

    public function __construct($options = null)
    {
        if (is_array($options)) {
            if (!isset($options['type'])) {
                throw new ConstraintDefinitionException('"type"オプションの指定は必須です。"datetime"または"number"が指定できます。');
            }
        }

        parent::__construct($options);
    }

}