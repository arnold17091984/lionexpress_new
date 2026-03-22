<?php

namespace Plugin\SEOAllOne\Form\Type\Admin;

use Plugin\SEOAllOne\Entity\SitemapConfig;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class SitemapConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
        ->add('Page', EntityType::class, [
            'class' => 'Eccube\Entity\Page',
            'choice_label' => 'id',
        ])
        ->add('changefreq', ChoiceType::class, [
            'label' => 'Change frequency',
            'required'  => false,
            'choices'   => array(
                '' => '',
                'always'  => 'always',
                'hourly'  => 'hourly',
                'daily'   => 'daily',
                'weekly'  => 'weekly',
                'monthly' => 'monthly',
                'yearly'  => 'yearly',
                'never'   => 'never',
            ),
        ])
        ->add('priority', ChoiceType::class, [
            'label' => 'Priority',
            'required' => false,
            'choices'   => array(
                '0.0' => 0.0,
                '0.1' => 0.1,
                '0.2' => 0.2,
                '0.3' => 0.3,
                '0.4' => 0.4,
                '0.5' => 0.5,
                '0.6' => 0.6,
                '0.7' => 0.7,
                '0.8' => 0.8,
                '0.9' => 0.9,
                '1.0' => 1.0,
            )
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SitemapConfig::class,
        ]);
    }
}
