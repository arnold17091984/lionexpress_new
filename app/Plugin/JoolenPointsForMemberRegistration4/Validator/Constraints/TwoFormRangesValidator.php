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
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class TwoFormRangesValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof TwoFormRanges) {
            throw new UnexpectedTypeException($constraint, TwoFormRanges::class);
        }

        if (!array_key_exists('start', $value) && !array_key_exists('end', $value)) {
            throw new ConstraintDefinitionException('"start" および "end"オプションのキーを指定してください。');
        }

        $start = $value['start'];
        $end = $value['end'];

        // いずれかが未入力の場合は、範囲バリデーションは不要のため処理終了
        if (null === $start || '' === $start) {
            return;
        }
        if (null === $end || '' === $end) {
            return;
        }

        if ($start > $end) {

            $message = function () use ($constraint): string {
                return $constraint->type === 'datetime' ?
                    $constraint->dateTimeMessage: $constraint->numberMessage;
            };

            $this->context
                ->buildViolation($message())
                ->addViolation();
        }
    }

}