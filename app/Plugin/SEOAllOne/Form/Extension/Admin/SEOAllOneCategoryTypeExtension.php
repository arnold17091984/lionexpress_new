<?php 

namespace Plugin\SEOAllOne\Form\Extension\Admin;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Eccube\Entity\Category;
use Eccube\Form\Type\Admin\CategoryType;

use Plugin\SEOAllOne\Repository\SEOAllOneCategoryRepository;
use Plugin\SEOAllOne\Entity\SEOAllOneCategory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Plugin\SEOAllOne\Repository\ConfigRepository;

class SEOAllOneCategoryTypeExtension extends AbstractTypeExtension {

	const CATEGORY_PARENTNAME = "##category.parentname##";
	const CATEGORY_NAME = "##category.name##";
	const CATEGORY_PARENTNAME_WITH_PARENTHESES = "##(category.parentname)##";

	public function __construct(SEOAllOneCategoryRepository $seoAllOneCategoryRepository, ConfigRepository $configRepository)
    {
		$this->seoAllOneCategoryRepository = $seoAllOneCategoryRepository;
		$this->configRepository = $configRepository;
		$this->product_category_help_text = '※上記title、description、keywordではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::CATEGORY_NAME.'（カテゴリ名）,'. self::CATEGORY_PARENTNAME .'（親カテゴリ名）, です。このうち、親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';
        $this->product_category_help_text_og = '※上記og:title、og:descriptionではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::CATEGORY_NAME.'（カテゴリ名）,'. self::CATEGORY_PARENTNAME .'（親カテゴリ名）, です。このうち、親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';
		$this->ogp_tooltips = '空白の場合は自動的に最適化表示されます';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	
		$Config = $this->configRepository->findOneBy([]);
		$Category = $builder->getData();
		$seo_parameter = $this->seoAllOneCategoryRepository->findOneBy(array('Category' => $Category));

		if (!$seo_parameter)
		{
			$seo_parameter = new SEOAllOneCategory();
		}

		if(!$seo_parameter->getOGType()) {
            $seo_parameter->setOGType('article');
        }
		$builder->add(
			'seoallone_title',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) Title',
				'mapped'   => false,
				'data'		=> $seo_parameter->getTitle(),
				'eccube_form_options'  => array(
					'auto_render'   => true
				),
				'help'	=> ''
			)
		);

		$builder->add(
			'seoallone_description',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) Description',
				'mapped'   => false,
				'data'		=> $seo_parameter->getDescription(),
				'eccube_form_options'  => array(
					'auto_render'   => true
				),
				'help'	=> ''
			)
		);

		$builder->add(
			'seoallone_keyword',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) Keyword',
				'mapped'   => false,
				'data'		=> $seo_parameter->getKeyword(),
				'eccube_form_options'  => array(
					'auto_render'   => true
				),
				'help'	=> $this->product_category_help_text,
			)
		);
		
		$builder->add(
			'seoallone_author',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) Author',
				'mapped'   => false,
				'data'		=> $seo_parameter->getAuthor(),
				'eccube_form_options'  => array(
					'auto_render'   => true
				)
			)
		);

		$builder->add(
			'seoallone_canonical',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) Canonical',
				'mapped'   => false,
				'data'		=> $seo_parameter->getCanonical(),
				'eccube_form_options'  => array(
					'auto_render'   => true
				)
			)
		);

		$builder->add(
			'seoallone_ogp_site_name',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:site_name',
				'mapped'   => false,
                'data'     => $seo_parameter->getOGSiteName(),
                // 'eccube_form_options'  => array(
                //     'auto_render'   => true
                // ),
            )
        );
        
        $builder->add(
			'seoallone_ogp_title',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:title',
				'mapped'   => false,
                'data'     => $seo_parameter->getOGTitle(),
                'eccube_form_options'  => array(
					'auto_render'   => true,
					'tooltips' => $this->ogp_tooltips
				),
				'help' => ''
            )
        );
        
        $builder->add(
			'seoallone_ogp_description',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:description',
				'mapped'   => false,
                'data'     => $seo_parameter->getOGDescription(),
                'eccube_form_options'  => array(
					'auto_render'   => true,
					'tooltips' => $this->ogp_tooltips
				),
				'help' => $this->product_category_help_text_og
            )
        );

        $builder->add(
			'seoallone_ogp_type',
			ChoiceType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:type',
				'mapped'   => false,
                'data'  => $seo_parameter->getOGType(),
                'choices'   => [
                    // ''          => '',
                    'website'   => 'website',
                    'product'   => 'product',
                    'article'   => 'article',
                    'blog'      => 'blog'
                ],
                'placeholder' => false,
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
            )
        );

        $builder->add(
			'seoallone_ogp_url',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:url',
				'mapped'   => false,
                'data'     => $seo_parameter->getOGUrl(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
            )
        );

        $builder->add(
			'seoallone_ogp_image',
			TextType::class,
			array(
				'required' => false,
				'label'    => '(SEO All One) og:image',
				'mapped'   => false,
                'data'     => $seo_parameter->getOGImage(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
            )
        );
    }

	/**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CategoryType::class;
	}
	
    /**
     * Return the class of the type being extended.
     */
    public static function getExtendedTypes(): iterable
    {
        return [CategoryType::class];
    }
}

?>