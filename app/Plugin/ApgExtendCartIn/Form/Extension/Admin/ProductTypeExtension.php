<?php

/*
 * This file is part of the ApgProductClassImage
 *
 * Copyright (C) 2018 ARCHIPELAGO Inc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApgExtendCartIn\Form\Extension\Admin;

use Eccube\Entity\Product;
use Eccube\Form\Type\Admin\ProductType;
use Plugin\ApgExtendCartIn\Entity\Config;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;
use Plugin\ApgExtendCartIn\Entity\Domain\ConfigSettingType;
use Plugin\ApgExtendCartIn\Repository\ConfigRepository;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

/**
 * カートタイプを保存するためのフォーム
 * Class ProductClassTypeExtension
 * @package Plugin\ApgProductClassImage\Form\Extension\Admin
 * @FormExtension
 */
class ProductTypeExtension extends AbstractTypeExtension
{

    /** @var ConfigRepository */
    protected $configRepository;

    /**
     * ProductClassTypeExtension constructor.
     * @param ConfigRepository $configRepository
     */
    public function __construct(
        ConfigRepository $configRepository
    )
    {
        $this->configRepository = $configRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Config $Config */
        $Config = $this->configRepository->getOrNew();
        if ($Config->getSettingType() === ConfigSettingType::INDIVIDUAL) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                /** @var Product $Product */
                $Product = $event->getData();
                if ($Product->hasProductClass()) {
                    $form = $event->getForm();
                    $form->add('apg_cart_type', ChoiceType::class, [
                        'label' => 'カートタイプ',
                        'choices' => array_flip(CartType::LABELS),
                        'eccube_form_options' => [
                            'auto_render' => true,
                        ],
                        'constraints' => [
                            new NotBlank(),
                            new GreaterThanOrEqual([
                                'value' => 1,
                            ]),
                            new Regex(['pattern' => '/^\d+$/']),
                        ],
                    ]);
                }
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }

    /**
     * Return the class of the type being extended.
     */
    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}