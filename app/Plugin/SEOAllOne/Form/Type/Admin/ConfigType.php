<?php

namespace Plugin\SEOAllOne\Form\Type\Admin;

use Plugin\SEOAllOne\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class ConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('rich_snippet_flg', ChoiceType::class, [
            'label' => 'リッチスニペット',
            'required' => true,
            'choices'  => [
                '有効' => 1,
                '無効' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('sns_flg', ChoiceType::class, [
            'label' => 'SNSボタン設定',
            'required' => true,
            'choices'  => [
                '有効' => 1,
                '無効' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('sitemap_flg', ChoiceType::class, [
            'label' => 'サイトマップ生成',
            'required' => true,
            'choices'  => [
                '有効' => 1,
                '無効' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('pagination_flg', ChoiceType::class, [
            'label' => '商品一覧ページのprev、nextタグ',
            'required' => true,
            'choices'  => [
                '有効' => 1,
                '無効' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('breadcrumb_flg', ChoiceType::class, [
            'label' => 'パンくずリスト',
            'required' => true,
            'choices'  => [
                '有効' => 1,
                '無効' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('facebook_flg', ChoiceType::class, [
            'label' => 'Facebook',
            'required' => true,
            'choices'  => [
                '表示' => 1,
                '非表示' => 0,
            ],
            'expanded' => true,
        ])
        ->add('twitter_flg', ChoiceType::class, [
            'label' => 'Twitter',
            'required' => true,
            'choices'  => [
                '表示' => 1,
                '非表示' => 0,
            ],
            'expanded' => true,
        ])
        ->add('line_flg', ChoiceType::class, [
            'label' => 'Line',
            'required' => true,
            'choices'  => [
                '表示' => 1,
                '非表示' => 0,
            ],
            'expanded' => true,
        ])
        ->add('shop_name_top_flg', ChoiceType::class, [
            'label' => 'トップページtitleタグの末尾に店名を表示',
            'required' => true,
            'choices'  => [
                '表示' => 1,
                '非表示' => 0,
            ],
            'expanded' => true,
        ]);
        $builder->add('global_id_flg', ChoiceType::class, [
            'label' => 'グローバル（商品固有） ID',
            'required' => true,
            'choices'  => [
                '商品コード自動追加する' => 1,
                '追加しない' => 0,
            ],
            'expanded' => true,
        ]);
		$builder->add(
			'global_id_type',
			ChoiceType::class,
			array(
				'required' => false,
				'label'    => 'グローバル識別子',
                'choices'   => [
                    'GTIN' => '1',
                    'GTIN-8' => '2',
                    'GTIN-13 (JAN)' => '3',
                    'GTIN-14' => '4',
                    'ISBN' => '5',
                    'MPN' => '6'
                ],
                'placeholder' => false,
                'eccube_form_options'  => array(
                    'auto_render'   => true
                )
            )
        );
        $builder->add('valid_price_flg', ChoiceType::class, [
            'label' => 'priceValidUntil',
            'required' => true,
            'choices'  => [
                '指定する' => 1,
                '指定なし' => 0,
            ],
            'expanded' => true,
        ]);
		
		$data = $builder->getData();
		$valid_price_flg = (int)$data['valid_price_flg'];
		if (isset($_POST['config'])){
			if (isset($_POST['config']['valid_price_flg'])){
				$valid_price_flg = (int)$_POST['config']['valid_price_flg'];
			}
		}
		$contraint = [];	
		if ($valid_price_flg) {
			$contraint = [
				new Assert\NotBlank(),
				new Assert\GreaterThan(0),
				new Assert\Regex([
					'pattern' => "/^\d+$/u",
					'message' => 'form_error.numeric_only',
				])
			];
		}

		$builder->add(
			'valid_price_month',
			TextType::class,
			array(
				'required' => false,
				'label'    => '',
				'constraints' => $contraint,
				'eccube_form_options'  => array(
					'auto_render'   => true
				)
			)
		);
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
