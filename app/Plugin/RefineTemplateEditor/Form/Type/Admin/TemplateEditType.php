<?php
/*
 * This file is part of Refine
 *
 * Copyright(c) 2022 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineTemplateEditor\Form\Type\Admin;

use Eccube\Form\Validator\TwigLint;
use Plugin\RefineTemplateEditor\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TemplateEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'mapped' => false,

            ])
            ->add('path', TextType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new Assert\NotBlank(),

                ],
            ])
            ->add('tpl_data', TextareaType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new TwigLint(),
                ],
            ]);

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'allow_extra_fields' => true,
        ]);
    }
}
