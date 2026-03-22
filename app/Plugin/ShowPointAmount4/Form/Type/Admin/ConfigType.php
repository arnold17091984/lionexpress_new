<?php

/*
 * This file is part of ShowPointAmount4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ShowPointAmount4\Form\Type\Admin;

use Plugin\ShowPointAmount4\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('option_show_in_product_detail', CheckboxType::class, [
                'label' => '商品詳細ページにポイント数を表示する',
                'required' => false,
            ])
            ->add('option_show_in_cart', CheckboxType::class, [
                'label' => 'ショッピングカートに合計ポイント数を表示する',
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
