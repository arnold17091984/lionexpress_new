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

namespace Plugin\JoolenPointsForMemberRegistration4\Form\Type\Admin;

use Plugin\JoolenPointsForMemberRegistration4\Validator\Constraints as JoolenAssert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RangeDateTimeType extends AbstractType
{
    /**
     * フォーム名指定キー定義
     */
    const START_NAME = 'start_name';    // 期間開始日時
    const END_NAME = 'end_name';        // 期間終了日時

    /**
     * オプション指定キー定義
     */
    const DATETIME_OPTIONS = 'datetime_options';

    /**
     * イベントリスナーのメソッド名定義
     */
    const VALIDATE_PERIOD_METHOD = 'validatePeriod';

    private const MAX_YEAR = 10;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * RangeDateTimeType constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(
        ValidatorInterface $validator
    )
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add($options[self::START_NAME], DateTimeType::class, $options[self::DATETIME_OPTIONS])
            ->add($options[self::END_NAME], DateTimeType::class, $options[self::DATETIME_OPTIONS])
            ->addEventListener(FormEvents::POST_SUBMIT, [$this, self::VALIDATE_PERIOD_METHOD]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            /** フォーム名指定キー */
            self::START_NAME => '',
            self::END_NAME => '',

            /** オプション指定キー */
            self::DATETIME_OPTIONS => [
                'required' => false,
                'date_widget' => 'single_text',
                'time_widget' => 'choice',
                'input' => 'datetime',
                'format' => 'yyyy-MM-dd HH:mm:ss',
                'years' => range(date('Y'), date('Y') + self::MAX_YEAR),
                'with_seconds' => true,
                'placeholder' => [
                    'hour' => '--', 'minute' => '--', 'second' => '--',
                ],
                'attr' => ['style' => 'height:46px'],
            ],
            /** symphony標準 */
            'inherit_data' => true,
            'error_bubbling' => false,
        ]);
    }

    /**
     * 期間のバリデーションを行う.
     *
     * @param FormEvent $event
     * @return void
     */
    public function validatePeriod(FormEvent $event): void
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();

        $startDate = $form->get($options[self::START_NAME])->getData();
        $endDate = $form->get($options[self::END_NAME])->getData();

        $errors = $this->validator->validate(['start' => $startDate, 'end' => $endDate], [
            new JoolenAssert\TwoFormRanges([
                'type' => 'datetime'
            ])
        ]);

        if ($errors->count() > 0) {
            foreach ($errors as $error) {
                $form->get($options[self::END_NAME])
                    ->addError(new FormError($error->getMessage()));
            }
        }
    }

}