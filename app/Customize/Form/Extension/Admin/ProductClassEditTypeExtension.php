<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ProductClassEditType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ProductClassEditType extension to add custom fields: unit, add_point, remarks.
 *
 * Core fields (checked, code, stock, price01, price02, tax_rate, etc.) are
 * already defined by Eccube\Form\Type\Admin\ProductClassEditType and must NOT
 * be re-declared here to avoid overriding core behaviour.
 */
class ProductClassEditTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('unit', TextType::class, [
                'required' => false,
            ])
            ->add('add_point', IntegerType::class, [
                'required' => false,
            ])
            ->add('remarks', TextType::class, [
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ProductClassEditType::class;
    }
}
