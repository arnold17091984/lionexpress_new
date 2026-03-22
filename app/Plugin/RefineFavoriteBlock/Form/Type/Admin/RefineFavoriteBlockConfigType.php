<?php

/*
 * This file is part of Refine
 *
 * Copyright(c) 2024 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineFavoriteBlock\Form\Type\Admin;

use Plugin\RefineFavoriteBlock\Entity\RefineFavoriteBlockConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RefineFavoriteBlockConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // 表示数
        $builder->add('display_num', IntegerType::class, [
            'label' => 'refine_favorite_block.admin.config.display_num',
            'required' => true,
            'attr' => [
                'min' => 1,
                'max' => 10,
            ],
            'constraints' => [
                new NotBlank(),
            ],
        ])
        // 文字の大きさ
        ->add('font_size', ChoiceType::class, [
            'label' => 'refine_favorite_block.admin.config.font_size',
            'choices' => array_flip([
                'xx-large' => 'xx-large' ,
                'x-large' => 'x-large',
                'large' => 'large',
                'medium' => 'medium',
                'small' => 'small',
                'x-small' => 'x-small',
                'xx-small' => 'xx-small',
            ]),
            'expanded' => false,
            'multiple' => false,
            'required' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RefineFavoriteBlockConfig::class,
        ]);
    }
}
