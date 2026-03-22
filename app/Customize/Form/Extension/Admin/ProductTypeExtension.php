<?php
namespace Customize\Form\Extension\Admin;

use Eccube\Form\Type\Admin\ProductType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

use Eccube\Form\Validator\TwigLint;

use Customize\Form\Extension\Master\ProductSizeExtension;
use Customize\Form\Extension\Master\ProductSexExtension;
use Customize\Form\Extension\Master\ProductConditionExtension;

class ProductTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('take_or_use', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ])
            ->add('side_effects', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ])
            ->add('prohibited', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ])
            ->add('storage', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ])
            ->add('maker', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ])
            ->add('shipping_country', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new TwigLint(),
                ],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        yield ProductType::class;
    }
}