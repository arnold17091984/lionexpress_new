<?php

namespace Plugin\ApgExtendCartIn\Form\Type\Admin;

use Plugin\ApgExtendCartIn\Entity\Config;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;
use Plugin\ApgExtendCartIn\Entity\Domain\ConfigSettingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cart_type', ChoiceType::class, [
            'choices' => array_flip(CartType::LABELS),
            'constraints' => [
//                new NotBlank(),
                new GreaterThanOrEqual([
                    'value' => 1,
                ]),
                new Regex(['pattern' => '/^\d+$/']),
            ],
        ]);

        $builder->add('setting_type', ChoiceType::class, [
            'choices' => array_flip(ConfigSettingType::LABELS),
            'constraints' => [
                new NotBlank(),
                new GreaterThanOrEqual([
                    'value' => 1,
                ]),
                new Regex(['pattern' => '/^\d+$/']),
            ],
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
