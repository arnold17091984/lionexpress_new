<?php

namespace Plugin\SEOAllOne;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Eccube\Common\EccubeConfig;
use Eccube\Event\TemplateEvent;
use Eccube\Event\EventArgs;
use Eccube\Repository\PageRepository;
use Eccube\Entity\Product;
use Eccube\Entity\Category;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Plugin\SEOAllOne\Entity\SEOAllOneDefault;
use Plugin\SEOAllOne\Entity\SEOAllOneProduct;
use Plugin\SEOAllOne\Entity\SEOAllOneCategory;
use Plugin\SEOAllOne\Entity\Config;
use Plugin\SEOAllOne\Repository\SEOAllOneDefaultRepository;
use Plugin\SEOAllOne\Repository\SEOAllOneProductRepository;
use Plugin\SEOAllOne\Repository\SEOAllOneCategoryRepository;
use Plugin\SEOAllOne\Repository\ConfigRepository;
use Plugin\SEOAllOne\Form\Type\Admin\SEOAllOneCategoryType;
use Plugin\SEOAllOne\Util\Util;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;


class Event implements EventSubscriberInterface
{
    const CONTENT_FOOTER_TAG  = '<!-- レイアウト設定 -->';

    const PRODUCT_NAME = "##product.name##";
    const PRODUCT_NAME_PATTERN = "/##(((?!#).)*)(product.name)(((?!#).)*)##/u";

    const PRODUCT_PRICE01 = "##product.list_price##";
    const PRODUCT_PRICE01_PATTERN = "/##(((?!#).)*)(product\.list_price)(((?!#).)*)##/u";

    const PRODUCT_PRICE02 = "##product.sell_price##";
    const PRODUCT_PRICE02_PATTERN = "/##(((?!#).)*)(product.sell_price)(((?!#).)*)##/u";

    const CATEGORY_NAME = "##category.name##";
    const CATEGORY_PARENTNAME = "##category.parentname##";
    const CATEGORY_PARENTNAME_WITH_PARENTHESES = "##(category.parentname)##";
    const CATEGORY_NAME_PATTERN = "/##(((?!#).)*)(category\.name)(((?!#).)*)##/u";
    const CATEGORY_PARENTNAME_PATTERN = "/##(((?!#).)*)(category\.parentname)(((?!#).)*)##/u";
    

    public function __construct(
        ContainerInterface $container,
        EccubeConfig    $eccubeConfig,
        SEOAllOneDefaultRepository $seoAllOneDefaultRepository,
        SEOAllOneProductRepository $seoAllOneProductRepository,
        SEOAllOneCategoryRepository $seoAllOneCategoryRepository,
        PageRepository $pageRepository,
        ConfigRepository $configRepository
    )
    {
        $this->container = $container;
        $this->eccubeConfig = $eccubeConfig;
        $this->seoAllOneDefaultRepository = $seoAllOneDefaultRepository;
        $this->seoAllOneProductRepository = $seoAllOneProductRepository;
        $this->seoAllOneCategoryRepository = $seoAllOneCategoryRepository;
        $this->pageRepository = $pageRepository;
        $this->configRepository = $configRepository;
        $this->product_detail_help_text = '※上記title、description、keywordではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::PRODUCT_NAME.'（商品名）,'. self::PRODUCT_PRICE01 .'（定価）, ' . self::PRODUCT_PRICE02 . '（販売価格）, ' . self::CATEGORY_NAME .'（カテゴリ名）、' .self::CATEGORY_PARENTNAME .'（親カテゴリ名）です。このうち、定価とカテゴリ名と親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';

        $this->product_detail_help_text_og = '※上記og:title、og:descriptionではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::PRODUCT_NAME.'（商品名）,'. self::PRODUCT_PRICE01 .'（定価）, ' . self::PRODUCT_PRICE02 . '（販売価格）, ' . self::CATEGORY_NAME .'（カテゴリ名）、' .self::CATEGORY_PARENTNAME .'（親カテゴリ名）です。このうち、定価とカテゴリ名と親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';

        $this->product_category_help_text = '※上記title、description、keywordではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::CATEGORY_NAME.'（カテゴリ名）,'. self::CATEGORY_PARENTNAME .'（親カテゴリ名）, です。このうち、親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';
        $this->product_category_help_text_og = '※上記og:title、og:descriptionではプレースホルダーが使用可能です。使用可能なプレースホルダーは '. self::CATEGORY_NAME.'（カテゴリ名）,'. self::CATEGORY_PARENTNAME .'（親カテゴリ名）, です。このうち、親カテゴリ名に関しては、データが存在しない場合、##で囲った中に書いたものは全て空白になります。例えば '. self::CATEGORY_PARENTNAME_WITH_PARENTHESES .' と記載すると、親カテゴリ名が存在しないとき##内の（）も併せて空白になります。';
        $this->ogp_tooltips = '空白の場合は自動的に最適化表示されます';

        $this->util = new Util($this->container);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'admin.content.page.edit.initialize' => 'onAdminContentPageEditInitialize',
            '@admin/Content/page_edit.twig'  => 'onAdminContentTwig',
            'admin.content.page.edit.complete'  => 'onAdminContentPageEditComplete',
            'admin.content.page.delete.complete'    => 'onAdminContentPageDeleteComplete',
            'front.product.detail.initialize'   => 'onFrontProductDetailInitalize',
            'Product/list.twig' => 'onProductListTwig',
            'admin.product.edit.initialize' => 'onAdminProductEditInitialize',
            '@admin/Product/product.twig'   => 'onAdminProductTwig',
            'admin.product.edit.complete'   => 'onAdminProductEditComplete',
            'admin.product.delete.complete' => 'onAdminProductDeleteComplete',
            'admin.product.category.index.complete'     => 'onAdminProductCategoryIndexComplete',
            'admin.product.category.delete.complete'    => 'onAdminProductCategoryDeleteComplete',
            '@admin/Product/category.twig'  => 'onAdminProductCategoryTwig',
            'index.twig'    => 'onIndexTwig',
            'Product/detail.twig' => 'onFrontProductDetailTwig',
            'front.product.index.search' => 'onFrontProductIndexInitialize',
			'@admin/Store/plugin.twig' => 'onAdminStorePluginIndexTwig'
        ];
    }

    public function onAdminContentPageEditInitialize(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        $Page = $event->getArgument('Page');
        if ($Page->getUrl() != 'homepage' && $Page->getUrl() != 'product_detail' && $Page->getUrl() != 'product_list')
        {
            return;
        }

        $seo_data = $this->seoAllOneDefaultRepository->findOneby(array('Page' => $Page));

        if (!$seo_data || !$seo_data->getOGType()) {
            $seo_data = new SEOAllOneDefault();
            if ($Page->getUrl() == 'homepage')
            {
                $seo_data->setOGType('website');
            }
            elseif ($Page->getUrl() == 'product_detail')
            {
                $seo_data->setOGType('product');
            }
            else
            {
                $seo_data->setOGType('article');
            }
        }
        if($Page->getUrl() == 'product_detail' || $Page->getUrl() == 'product_list') {
            $seoallone_title_opt = array(
                'required'  => false,
                'label' => 'Title',
                'mapped'    => false,
                'data'  => $seo_data->getTitle()
            );
    
            $seoallone_description_opt = array(
                'required'  => false,
                'label' => 'Description',
                'mapped'    => false,
                'data'  => $seo_data->getDescription()
            );
    
            $seoallone_keyword_opt = array(
                'required'  => false,
                'label' => 'Keyword',
                'mapped'    => false,
                'data'  => $seo_data->getKeyword()
            );
    
            $seoallone_author_opt = array(
                'required'  => false,
                'label' => 'Author',
                'mapped'    => false,
                'data'  => $seo_data->getAuthor()
            );
    
            $seoallone_canonical_opt = array(
                'required'  => false,
                'label' => 'Canonical',
                'mapped'    => false,
                'data'  => $seo_data->getCanonical()
            );
        }

        $seoallone_ogp_title_opt = array(
            'required'  => false,
            'label' => 'og:title',
            'mapped'    => false,
            'data'  => $seo_data->getOGTitle()
        );
        $seoallone_ogp_description_opt = array(
            'required'  => false,
            'label' => 'og:description',
            'mapped'    => false,
            'data'  => $seo_data->getOGDescription()
        );

        $seoallone_ogp_url_opt = array(
            'required'  => false,
            'label' => 'og:url',
            'mapped'    => false,
            'data'  => $seo_data->getOGUrl()
        );

        $seoallone_ogp_type_opt = array(
            'required'  => false,
            'label' => 'og:type',
            'mapped'    => false,
            'data'  => $seo_data->getOGType(),
            'choices'   => [
                // ''          => '',
                'website'   => 'website',
                'product'   => 'product',
                'article'   => 'article',
                'blog'      => 'blog'
            ],
            'placeholder' => false
        );

        if ($Page->getUrl() == 'product_detail')
        {
            $seoallone_keyword_opt['help'] = $this->product_detail_help_text;
            $seoallone_ogp_description_opt['help'] = $this->product_detail_help_text_og;

        }

        if ($Page->getUrl() == 'product_list')
        {
            $seoallone_keyword_opt['help'] = $this->product_category_help_text;
            $seoallone_ogp_description_opt['help'] = $this->product_category_help_text_og;
        }


        $builder = $event->getArgument('builder');
        $builder
        ->add('seoallone_ogp_title', TextType::class, $seoallone_ogp_title_opt)
        ->add('seoallone_ogp_description', TextType::class, $seoallone_ogp_description_opt)
        ->add('seoallone_ogp_url', TextType::class, $seoallone_ogp_url_opt)
        ->add('seoallone_ogp_type', ChoiceType::class, $seoallone_ogp_type_opt);

        if($Page->getUrl() == 'product_detail' || $Page->getUrl() == 'product_list') {
            $builder
            ->add('seoallone_title', TextType::class, $seoallone_title_opt)
            ->add('seoallone_author', TextType::class, $seoallone_author_opt)
            ->add('seoallone_description', TextType::class, $seoallone_description_opt)
            ->add('seoallone_keyword', TextType::class, $seoallone_keyword_opt)
            ->add('seoallone_canonical', TextType::class, $seoallone_canonical_opt);
        }
    }

    public function onAdminContentTwig(TemplateEvent $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        $parameters = $event->getParameters();
        $page_id = $parameters['page_id'];

        if ($page_id != 1 && $page_id != 2 && $page_id != 3)
        {
            return;
        }

        $source = $event->getSource();
        $snippet_seo = $this->container->get('twig')->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_default_form.twig');

        if (strpos($source, self::CONTENT_FOOTER_TAG)) {
            $search_tag = self::CONTENT_FOOTER_TAG;
            $replace_layout = $snippet_seo->getCode().$search_tag;
            $source = str_replace($search_tag, $replace_layout, $source);
            $event->setSource($source);
        }
    }

    public function onAdminContentPageEditComplete(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);

        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }

        $form = $event->getArgument('form');
        $Page = $event->getArgument('Page');

        if ($Page->getUrl() != 'homepage' && $Page->getUrl() != 'product_detail' && $Page->getUrl() != 'product_list')
        {
            return;
        }

        $em = $this->container->get('doctrine.orm.entity_manager');

        $seoallone_default = $this->seoAllOneDefaultRepository->findOneby(array('Page' => $Page));

        if (!$seoallone_default) {
			$seoallone_default = new SEOAllOneDefault();
        }
        else
        {
            $seoallone_default->setUpdateDate(new \DateTime());
        }

        if ($Page->getUrl() == 'product_detail' || $Page->getUrl() == 'product_list')
        {
            $seo_title          = $form['seoallone_title']->getData();
            $seo_author         = $form['seoallone_author']->getData();
            $seo_description    = $form['seoallone_description']->getData();
            $seo_keyword        = $form['seoallone_keyword']->getData();
            $seo_canonical      = $form['seoallone_canonical']->getData();
        }

        $ogp_title         = $form['seoallone_ogp_title']->getData();
		$ogp_description    = $form['seoallone_ogp_description']->getData();
        $ogp_url        = $form['seoallone_ogp_url']->getData();
        $ogp_type      = $form['seoallone_ogp_type']->getData();
        
        if ($Page->getUrl() == 'product_detail' || $Page->getUrl() == 'product_list')
        {
            $seoallone_default->setTitle($seo_title);
            $seoallone_default->setAuthor($seo_author);
            $seoallone_default->setDescription($seo_description);
            $seoallone_default->setKeyword($seo_keyword);
            $seoallone_default->setCanonical($seo_canonical);
        }
        $seoallone_default->setOGTitle($ogp_title);
        $seoallone_default->setOGDescription($ogp_description);
        $seoallone_default->setOGUrl($ogp_url);
        $seoallone_default->setOGType($ogp_type);
        $seoallone_default->setPage($Page);
        $seoallone_default->setDelFlg(0);

        $em->persist($seoallone_default);
		$em->flush($seoallone_default);
    }

    /**
     * @param EventArgs $event
	 * @return mixed
     * @throws Exception\RedirectException
     */
    public function onFrontProductDetailInitalize(EventArgs $event)
	{
        $PageProductDetail = $this->pageRepository->findOneBy(array('url' => 'product_detail'));
        $Config = $this->configRepository->findOneBy([]);
        if (!$PageProductDetail)
        {
            return;
        }

        $Product = $event->getArgument('Product');

        $json_category = array();
        foreach ($Product['ProductCategories'] as $key1 => $ProductCategory) {
            $category_str = '';
            foreach ($ProductCategory['Category']['path'] as $key2 => $Category) {
                $category_str .= $Category['name'] . "/";
            }
            $json_category[] = trim($category_str, '/');
        }

        $Product->json_category = $json_category;
        
		$default_seo_parameter = $this->seoAllOneDefaultRepository->findOneby(array('Page' => $PageProductDetail));
        $individual_seo_parameter = $this->seoAllOneProductRepository->findOneBy(array('Product' => $Product));

        if (!$default_seo_parameter && !$individual_seo_parameter)
        {
            return;
        }

        $Product->seo_title = null;
        $Product->seo_another = null;
        $Product->seo_description = null;
        $Product->seo_keywords = null;
        $Product->seo_canonical = null;
        $Product->og_title = null;
        $Product->og_description = null;
        $Product->og_site_name = null;
        $Product->og_type = null;
        $Product->og_url = null;
        $Product->og_image = null;
        $Product->noindex_flg = null;
        $Product->og_image_metatag = true;
        $Product->global_id = null;
        $Product->global_id_type = null;	
        $Product->valid_price_until = null;		
        $Config = $this->configRepository->findOneBy([]);
        if ($default_seo_parameter)
        {
            $Product->seo_title = $default_seo_parameter->getTitle();
            $Product->seo_another = $default_seo_parameter->getAuthor();
            $Product->seo_description = $default_seo_parameter->getDescription();
            $Product->seo_keywords = $default_seo_parameter->getKeyword();
            $Product->seo_canonical = $default_seo_parameter->getCanonical();
            $Product->og_title   = $default_seo_parameter->getOGTitle();
            $Product->og_description  = $default_seo_parameter->getOGDescription();
            $Product->og_type  = $default_seo_parameter->getOGType();
            $Product->og_url  = $default_seo_parameter->getOGUrl();
            $Product->og_image  = $default_seo_parameter->getOGImage();
        }
		
		if ($Config->getGlobalIdFlg()) {
			$Product->global_id_type = $Config->getValidPriceFlg();
		}
		
		if ($Config->getValidPriceFlg() && $Config->getValidPriceMonth()) {
			$valid_price_month = (int)$Config->getValidPriceMonth();
			$Product->valid_price_until = date('Y-m-d', strtotime("+{$valid_price_month} months", strtotime(date('Y-m-d'))));
		} 
        
        if ($individual_seo_parameter)
        {
            if ($individual_seo_parameter->getRedirectUrl() != '')
            {
                $response = $individual_seo_parameter->getRedirectUrl();
                // redirect using exception
                throw new Exception\RedirectException(
                    new \Symfony\Component\HttpFoundation\RedirectResponse(
                        $response, 301
                    ),
                    '',
                    301
                );
            }

            if ($individual_seo_parameter->getNoindexFlg()) {
                $Product->noindex_flg = $individual_seo_parameter->getNoindexFlg();
            }

            if ($individual_seo_parameter->getTitle()) {
                $Product->seo_title = $individual_seo_parameter->getTitle();
            } 

            if ($individual_seo_parameter->getAuthor()) {
                $Product->seo_another = $individual_seo_parameter->getAuthor();
            } 

            if ($individual_seo_parameter->getDescription()) {
                $Product->seo_description = $individual_seo_parameter->getDescription();
            } 

            if ($individual_seo_parameter->getKeyword()) {
                $Product->seo_keywords = $individual_seo_parameter->getKeyword();
            } 

            if ($individual_seo_parameter->getCanonical()) {
                $Product->seo_canonical = $individual_seo_parameter->getCanonical();
            }
            else
            {
                $Product->seo_canonical = $this->container->get('router')->generate('product_detail', ['id' => $Product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            } 

            if ($individual_seo_parameter->getOGTitle()) {
                $Product->og_title = $individual_seo_parameter->getOGTitle();
            } else if($individual_seo_parameter->getTitle()) {
                $Product->og_title = $individual_seo_parameter->getTitle();
            }

            if ($individual_seo_parameter->getOGDescription()) {
                $Product->og_description = $individual_seo_parameter->getOGDescription();
            } else if($individual_seo_parameter->getDescription()) {
                $Product->og_description = $individual_seo_parameter->getDescription();
            }

            if ($individual_seo_parameter->getOGSiteName()) {
                $Product->og_site_name = $individual_seo_parameter->getOGSiteName();
            } 

            if ($individual_seo_parameter->getOGType()) {
                $Product->og_type = $individual_seo_parameter->getOGType();
            } 

            if ($individual_seo_parameter->getOGUrl()) {
                $Product->og_url = $individual_seo_parameter->getOGUrl();
            } 

            if ($individual_seo_parameter->getOGImage()) {
                $Product->og_image = $individual_seo_parameter->getOGImage();
            } 

            if ($Config->getGlobalIdFlg()) {
				$global_id_type = '';
				if ($individual_seo_parameter->getUpdatedFlg()) {
					$global_id_type = $individual_seo_parameter->getGlobalIdType();
					if (!$individual_seo_parameter->getGlobalId()){
						$Product->global_id = $Product->getCodeMin();
					} else {
						$Product->global_id = $individual_seo_parameter->getGlobalId();
					}
				} else {
					$global_id_type = $Config->getGlobalIdType();
					$Product->global_id = $Product->getCodeMin();
				}
				
				if ($global_id_type == 1) {
					$Product->global_id_type = 'gtin';
				} elseif ($global_id_type == 1) {
					$Product->global_id_type = 'gtin';
				} elseif ($global_id_type == 2) {
					$Product->global_id_type = 'gtin8';
				} elseif ($global_id_type == 3) {
					$Product->global_id_type = 'gtin13';
				} elseif ($global_id_type == 4) {
					$Product->global_id_type = 'gtin14';
				}                 
            } 
			
			if ($Config->getValidPriceFlg()) {
				$valid_price_month = $individual_seo_parameter->getValidPriceMonth();
				if ($valid_price_month) {
					$Product->valid_price_until = date('Y-m-d', strtotime("+{$valid_price_month} months", strtotime(date('Y-m-d'))));
				}
			} 
        } else {
			if ($Config->getGlobalIdFlg()) {
				$global_id_type = $Config->getGlobalIdType();
				if ($global_id_type == 1) {
					$Product->global_id_type = 'gtin';
				} elseif ($global_id_type == 1) {
					$Product->global_id_type = 'gtin';
				} elseif ($global_id_type == 2) {
					$Product->global_id_type = 'gtin8';
				} elseif ($global_id_type == 3) {
					$Product->global_id_type = 'gtin13';
				} elseif ($global_id_type == 4) {
					$Product->global_id_type = 'gtin14';
				} 
				$Product->global_id = $Product->getCodeMin();
			}
		}


        $Product = $this->readyPlaceholderProduct($Product);

        $meta_tags = $PageProductDetail->getMetaTags();
        if ($meta_tags) {
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:title"[^>]+(\/)?>/m', '', $meta_tags);
            if ($Product->og_description || $Product->seo_description || $PageProductDetail->getDescription()) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:description"[^>]+(\/)?>/m', '', $meta_tags);
            }

            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:url"[^>]+(\/)?>/m', '', $meta_tags);
            if($Product->og_image || $Product->getMainListImage()) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', '', $meta_tags);
                $Product->og_image_metatag = false;
            } 
            if( $Product->og_image_metatag == true && !preg_match('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', $meta_tags, $matches)) {
                $Product->og_image_metatag = false;
            }
            
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:type"[^>]+(\/)?>/m', '', $meta_tags);
            $meta_tags = str_replace("\r\n\r\n", "", $meta_tags);
            $meta_tags = str_replace("\n\r\n\r", "", $meta_tags);
        } else {
            $Product->og_image_metatag = false;
        }
        $PageProductDetail->setMetaTags($meta_tags);

        
    }
    
    public function onProductListTwig(TemplateEvent $event)
    {
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getMasterRequest();
        $parameters = $event->getParameters();

        $strApiJsonParame = "";

        if ($request->getMethod() === 'GET') {
            $all = $request->query->all();
            if (array_key_exists('pageno', $all) && $all['pageno'] == 0) {
                $all['pageno'] = 1;
            }
            $strApiJsonParame = "?" . http_build_query($all);
        }

        $event->setParameter('strApiJsonParame', $strApiJsonParame);
        $parameters['strApiJsonParame'] = $strApiJsonParame;

        $this->readyPagination($parameters);

        $Config = $this->configRepository->findOneBy([]);
        $parameters['SEOAllOneConfig'] = $Config;
        $event->setParameter('SEOAllOneConfig', $Config);
        // if ($Config->getRichSnippetFlg() == 1)
        // {
        //     $json_breadcrumb_twig = '@SEOAllOne/json-ld-breadcrumb.twig';
        //     $event->addSnippet($json_breadcrumb_twig);
        // }

        if (!isset($parameters['Category']) || $parameters['Category'] == NULL)
        {
            $seo_title = !empty($parameters['title'])? $parameters['title'] : '';
            $seo_subtitle = !empty($parameters['subtitle'])? $parameters['subtitle'] : '';
            $seo_link_prev = !empty($parameters['seo_link_prev'])? $parameters['seo_link_prev'] : '';
            $seo_link_next = !empty($parameters['seo_link_next'])? $parameters['seo_link_next'] : '';
            $request->attributes->set('seo_title', $seo_title);
            $request->attributes->set('seo_subtitle', $seo_subtitle);
            $request->attributes->set('seo_link_prev', $seo_link_prev);
            $request->attributes->set('seo_link_next', $seo_link_next);
            
            // Fix bug canonical in product_list page
            $parameters['Category'] = New Category();
            // return;
        }

        $PageProductCategory = $this->pageRepository->findOneBy(array('url' => 'product_list'));

        if (!$PageProductCategory)
        {
            return;
        }

        if(is_null($Config)) {
            return;
        }

        $Category = $parameters['Category'];

        $default_seo_parameter = $this->seoAllOneDefaultRepository->findOneby(array('Page' => $PageProductCategory));

        $individual_seo_parameter = $this->seoAllOneCategoryRepository->findOneBy(array('Category' => $Category));

        if (!$default_seo_parameter && !$individual_seo_parameter)
        {
            return;
        }

        $Category->seo_title = null;
        $Category->seo_another = null;
        $Category->seo_description = null;
        $Category->seo_keywords = null;
        $Category->seo_canonical = null;
        $Category->og_title = null;
        $Category->og_description = null;
        $Category->og_site_name = null;
        $Category->og_type = null;
        $Category->og_url = null;
        $Category->og_image = null;
        $Category->og_image_metatag = true;
        if ($default_seo_parameter)
        {
            $Category->seo_title = $default_seo_parameter->getTitle();
            $Category->seo_another = $default_seo_parameter->getAuthor();
            $Category->seo_description = $default_seo_parameter->getDescription();
            $Category->seo_keywords = $default_seo_parameter->getKeyword();
            $Category->seo_canonical = $default_seo_parameter->getCanonical();
            $Category->og_title   = $default_seo_parameter->getOGTitle();
            $Category->og_description  = $default_seo_parameter->getOGDescription();
            $Category->og_site_name  = $default_seo_parameter->getOGSiteName();
            $Category->og_type  = $default_seo_parameter->getOGType();
            $Category->og_url  = $default_seo_parameter->getOGUrl();
            $Category->og_image  = $default_seo_parameter->getOGImage();
        }
        
        if ($individual_seo_parameter)
        {
            if ($individual_seo_parameter->getTitle()) {
                $Category->seo_title = $individual_seo_parameter->getTitle();
            } 

            if ($individual_seo_parameter->getAuthor()) {
                $Category->seo_another = $individual_seo_parameter->getAuthor();
            } 

            if ($individual_seo_parameter->getDescription()) {
                $Category->seo_description = $individual_seo_parameter->getDescription();
            } 

            if ($individual_seo_parameter->getKeyword()) {
                $Category->seo_keywords = $individual_seo_parameter->getKeyword();
            } 

            if ($individual_seo_parameter->getCanonical()) {
                $Category->seo_canonical = $individual_seo_parameter->getCanonical();
            } 

            if ($individual_seo_parameter->getOGTitle()) {
                $Category->og_title = $individual_seo_parameter->getOGTitle();
            } else if ($individual_seo_parameter->getTitle()) {
                $Category->og_title = $individual_seo_parameter->getTitle();
            }

            if ($individual_seo_parameter->getOGDescription()) {
                $Category->og_description = $individual_seo_parameter->getOGDescription();
            } else if ($individual_seo_parameter->getDescription()) {
                $Category->og_description = $individual_seo_parameter->getDescription();
            }

            if ($individual_seo_parameter->getOGSiteName()) {
                $Category->og_site_name = $individual_seo_parameter->getOGSiteName();
            } 

            if ($individual_seo_parameter->getOGType()) {
                $Category->og_type = $individual_seo_parameter->getOGType();
            } 

            if ($individual_seo_parameter->getOGUrl()) {
                $Category->og_url = $individual_seo_parameter->getOGUrl();
            } 

            if ($individual_seo_parameter->getOGImage()) {
                $Category->og_image = $individual_seo_parameter->getOGImage();
            } 

        }
        
        $Category = $this->readyPlaceholderCategory($Category);
        
        $meta_tags = $PageProductCategory->getMetaTags();

        if ($meta_tags) {
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:title"[^>]+(\/)?>/m', '', $meta_tags);
            if ($Category->og_description || $Category->seo_description || $PageProductCategory->getDescription()) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:description"[^>]+(\/)?>/m', '', $meta_tags);
            }
            $paginationData = $parameters['pagination']->getPaginationData();
            if ($Category->og_image) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', '', $meta_tags);
                $Category->og_image_metatag = false;
            } else if ($paginationData['totalCount'] >= 1) {
                foreach($parameters['pagination'] as $Product) {
                    if($Product->getMainListImage()) {
                        $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', '', $meta_tags);
                        $Category->og_image_metatag = false;
                        break;
                    }
                }
            } 
            
            if($Category->og_image_metatag == true && !preg_match('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', $meta_tags, $matches)) {
                $Category->og_image_metatag = false;
            }
            
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:url"[^>]+(\/)?>/m', '', $meta_tags);
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:type"[^>]+(\/)?>/m', '', $meta_tags);
            $meta_tags = str_replace("\r\n\r\n", "", $meta_tags);
            $meta_tags = str_replace("\n\r\n\r", "", $meta_tags);
        } else {
            $Category->og_image_metatag = false;
        }
        $event->setParameters($parameters);
        $PageProductCategory->setMetaTags($meta_tags);
        

        $request->attributes->set('seo_Category', $Category);
        $seo_title = !empty($parameters['title'])? $parameters['title'] : '';
        $seo_subtitle = !empty($parameters['subtitle'])? $parameters['subtitle'] : '';
        $seo_link_prev = !empty($parameters['seo_link_prev'])? $parameters['seo_link_prev'] : '';
        $seo_link_next = !empty($parameters['seo_link_next'])? $parameters['seo_link_next'] : '';
        $request->attributes->set('seo_title', $seo_title);
        $request->attributes->set('seo_subtitle', $seo_subtitle);
        $request->attributes->set('seo_link_prev', $seo_link_prev);
        $request->attributes->set('seo_link_next', $seo_link_next);

        
    }

    public function onAdminProductEditInitialize(EventArgs $event)
	{
        $Config = $this->configRepository->findOneBy([]);
        $Product = $event->getArgument('Product');

		$seo_parameter = $this->seoAllOneProductRepository->findOneby(array('Product' => $Product));

		if (!$seo_parameter) {
            $seo_parameter = new SEOAllOneProduct();
        }
        
        if(!$seo_parameter->getOGType()) {
            $seo_parameter->setOGType('product');
        }

        $builder = $event->getArgument('builder');

        $builder->add(
            'seoallone_title',
            TextType::class,
            array(
                'required' => false,
                'label'    => '(SEO All One) Title',
                'mapped'   => false,
                'data'     => $seo_parameter->getTitle(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
                'help'      => ''
            )
        );
        $builder->add(
            'seoallone_description',
            TextType::class,
            array(
                'required' => false,
                'label'    => '(SEO All One) Description',
                'mapped'   => false,
                'data'     => $seo_parameter->getDescription(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
                'help'      => ''
            )
        );
        $builder->add(
            'seoallone_keyword',
            TextType::class,
            array(
                'required' => false,
                'label'    => '(SEO All One) Keyword',
                'mapped'   => false,
                'data'     => $seo_parameter->getKeyword(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
                'help'      => $this->product_detail_help_text,
            )
        );
        $builder->add(
            'seoallone_author',
            TextType::class,
            array(
                'required' => false,
                'label'    => '(SEO All One) Author',
                'mapped'   => false,
                'data'     => $seo_parameter->getAuthor(),
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
                'data'     => $seo_parameter->getCanonical(),
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
                'help'  => '※空欄の場合は現在のURLがそのままcanonicalに表示されます。'
            )
        );

        //ogp
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
                'help' => $this->product_detail_help_text_og
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
        
        $builder->add(
            'seoallone_noindex',
            ChoiceType::class,
            array(
                'required' => false,
                'label'    => '(SEO All One) noindex設定',
                'mapped'   => false,
                'data'      => $seo_parameter->getNoindexFlg(),
                'choices'   => [
                    'noindexをつける' => 1,
                    'noindexをつけない' => 0,
                ],
                'placeholder' => false,
                'eccube_form_options'  => array(
                    'auto_render'   => true
                ),
                'expanded' => true,
            )
        );

        $builder->add(
            'seoallone_redirect_url',
            TextType::class,
            array(
                'required'  => false,
                'label'     => '(SEO All One) リダイレクトURL',
                'mapped'    => false,
                'data'      => $seo_parameter->getRedirectUrl(),
                'eccube_form_options'  => array(
                    'auto_render'   => TRUE
                ),
                'help'  => '※こちらにURLを指定すると、詳細ページで301リダイレクトが発生するようになり、一覧ページにはこの商品は表示されません。'
            )
        );

		if ($Config->getGlobalIdFlg()) {
			$global_id_type = $Config->getGlobalIdType();
			$updated_flg = $seo_parameter->getUpdatedFlg();
			
			if($seo_parameter->getGlobalIdType()) {
				$global_id_type = $seo_parameter->getGlobalIdType();
			}
			$builder->add(
				'seoallone_global_id_type',
				ChoiceType::class,
				array(
					'required' => false,
					'label'    => '(SEO All One) バーコード種類',
					'choices'   => [
						'GTIN' => '1',
						'GTIN-8' => '2',
						'GTIN-13 (JAN)' => '3',
						'GTIN-14' => '4',
						'ISBN' => '5',
						'MPN' => '6'
					],
					'mapped'   => false,
					'data'     => $global_id_type,
					'placeholder' => false,
					'eccube_form_options'  => array(
						'auto_render'   => true
					)
				)
			);
			
//			$globalId = '';			
//			if (!$updated_flg) { // in case has not updated, set product code for global id at default
//				$globalId = $Product->getCodeMin();
//			} else {
//				$globalId = $seo_parameter->getGlobalId();
//			}
//			if ($_POST) {
//				$globalId = $seo_parameter->getGlobalId();
//			}
			
			$builder->add(
				'seoallone_global_id',
				TextType::class,
				array(
					'required' => false,
					'label'    => '(SEO All One) 固有商品 ID',
					'mapped'   => false,
					'data'     => $seo_parameter->getGlobalId(),
					'eccube_form_options'  => array(
						'auto_render'   => true
					),
				)
			);
		}

		if ($Config->getValidPriceFlg()) {
			$valid_price_month = (int)$Config->getValidPriceMonth();
			if ($valid_price_month==0) {
				$valid_price_month = "";
			}
			if($seo_parameter->getValidPriceMonth() && $seo_parameter->getValidPriceMonth()!=$valid_price_month) {
				$valid_price_month = $seo_parameter->getValidPriceMonth();
			}
			$builder->add(
				'seoallone_valid_price_month',
				TextType::class,
				array(
					'required' => false,
					'label'    => '(SEO All One) priceValidUntil',
					'mapped'   => false,
					'data'     => $valid_price_month,
					'eccube_form_options'  => array(
						'auto_render'   => true
					),
					'attr' => ['style' => 'width: 100px; display: inline'],
					'constraints' => [
						new Assert\GreaterThan(0),
						new Assert\Regex([
							'pattern' => "/^\d+$/u",
							'message' => 'form_error.numeric_only',
						])
					],
					'label_format' => ' ヶ月',
					'help' => '※Googleの価格の有効期限情報です。基本的には変更する必要はありません。'
				)
			);
			
			
		}
    }

    public function onAdminProductTwig(TemplateEvent $event)
    {
        $Config = $this->configRepository->findOneBy([]);
       
        $twig = $this->container->get('twig');
        $search_code = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_1.twig');
        $replace_code = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_2.twig');
        $source = $event->getSource();

        if (strpos($source, str_replace("\r", '', $search_code->getCode())))
        {
            $view_src = str_replace(str_replace("\r", '', $search_code->getCode()), $replace_code->getCode(), $source);
            $event->setSource($view_src);
            return;
        }

        $search_code = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_1_1.twig');
        $replace_code = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_2_1.twig');

        if (strpos($source, str_replace("\r", '', $search_code->getCode())))
        {
            $view_src = str_replace(str_replace("\r", '', $search_code->getCode()), $replace_code->getCode(), $source);
            $event->setSource($view_src);
            return;
        }
    }
    
    public function onAdminProductEditComplete(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        $form = $event->getArgument('form');
		$Product = $event->getArgument('Product');

		$seoallone_product = $this->seoAllOneProductRepository->findOneby(array('Product' => $Product));
		if (!$seoallone_product) {
			$seoallone_product = new SEOAllOneProduct();
		} else {
			//中間テーブルにデータが有る時
			$seoallone_product->setUpdateDate(new \DateTime());
        }
        
        $em = $this->container->get('doctrine.orm.entity_manager');

        $seo_title          = $form['seoallone_title']->getData();
        $seo_author         = $form['seoallone_author']->getData();
        $seo_description    = $form['seoallone_description']->getData();
        $seo_keyword        = $form['seoallone_keyword']->getData();
        $seo_canonical      = $form['seoallone_canonical']->getData();

        $ogp_site_name      = $form['seoallone_ogp_site_name']->getData();
        $ogp_title          = $form['seoallone_ogp_title']->getData();
        $ogp_description    = $form['seoallone_ogp_description']->getData();
        $ogp_url            = $form['seoallone_ogp_url']->getData();
        $ogp_type           = $form['seoallone_ogp_type']->getData();
        $ogp_image          = $form['seoallone_ogp_image']->getData();

        $seoallone_product->setTitle($seo_title);
        $seoallone_product->setAuthor($seo_author);
        $seoallone_product->setDescription($seo_description);
        $seoallone_product->setKeyword($seo_keyword);
        $seoallone_product->setCanonical($seo_canonical);

        $seoallone_product->setOGSiteName($ogp_site_name);
        $seoallone_product->setOGTitle($ogp_title);
        $seoallone_product->setOGDescription($ogp_description);
        $seoallone_product->setOGUrl($ogp_url);
        $seoallone_product->setOGType($ogp_type);
        $seoallone_product->setOGImage($ogp_image);
        $seoallone_product->setUpdatedFlg(1);
		
		$global_id_type = 0;
		if ($Config->getGlobalIdFlg()) {
			$global_id_type = $form['seoallone_global_id_type']->getData();
			$global_id = $form['seoallone_global_id']->getData();
			$seoallone_product->setGlobalId($global_id);
		}
		$seoallone_product->setGlobalIdType($global_id_type);
		
		$valid_price_month = 0;
		if ($Config->getValidPriceFlg()) {
			$valid_price_month = (int)$form['seoallone_valid_price_month']->getData();
		}
		$seoallone_product->setValidPriceMonth($valid_price_month);

        $noindex_flg = $form['seoallone_noindex']->getData();
        $seoallone_product->setNoindexFlg($noindex_flg);


        $redirect_url       = $form['seoallone_redirect_url']->getData();
        $seoallone_product->setRedirectUrl($redirect_url);

        $seoallone_product->setProduct($Product);
        $seoallone_product->setDelFlg(0);
        
        
        $em->persist($seoallone_product);
        $em->flush($seoallone_product);
        
        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }
    }

    public function onAdminProductCategoryIndexComplete(EventArgs $event)
	{
        $Config = $this->configRepository->findOneBy([]);
        
        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }

        if ($event->hasArgument('editForm'))
        {
            $form = $event->getArgument('editForm');
        }
        else
        {
            $form = $event->getArgument('form');
        }

		$Parent = $event->getArgument('Parent');
		$TargetCategory = $event->getArgument('TargetCategory');
		$category_seo = $this->seoAllOneCategoryRepository->findOneBy(array('Category' => $TargetCategory));
		if (!$category_seo) {
			$category_seo = new SEOAllOneCategory();
		} else {
			//中間テーブルにデータが有る時
			$category_seo->setUpdateDate(new \DateTime());
        }
        
        $em = $this->container->get('doctrine.orm.entity_manager');

		// 入力を取り出す
		$seo_title = $form['seoallone_title']->getData();
		$seo_author = $form['seoallone_author']->getData();
		$seo_description = $form['seoallone_description']->getData();
		$seo_keyword = $form['seoallone_keyword']->getData();
        $seo_canonical = $form['seoallone_canonical']->getData();

        $ogp_site_name          = $form['seoallone_ogp_site_name']->getData();
        $ogp_title         = $form['seoallone_ogp_title']->getData();
		$ogp_description    = $form['seoallone_ogp_description']->getData();
        $ogp_url        = $form['seoallone_ogp_url']->getData();
        $ogp_type      = $form['seoallone_ogp_type']->getData();
        $ogp_image      = $form['seoallone_ogp_image']->getData();
        
		//入力値セット
		$category_seo->setTitle($seo_title);
		$category_seo->setAuthor($seo_author);
		$category_seo->setDescription($seo_description);
		$category_seo->setKeyword($seo_keyword);
        $category_seo->setCanonical($seo_canonical);

        $category_seo->setOGSiteName($ogp_site_name);
        $category_seo->setOGTitle($ogp_title);
        $category_seo->setOGDescription($ogp_description);
        $category_seo->setOGUrl($ogp_url);
        $category_seo->setOGType($ogp_type);
        $category_seo->setOGImage($ogp_image);

        $category_seo->setCategory($TargetCategory);
        $category_seo->setDelFlg(0);
        

		// DB更新
		$em->persist($category_seo);
		$em->flush($category_seo);
    }

    private function readyPlaceholderProduct(Product $Product)
    {
        $categories = $Product->getProductCategories();
        $category_name = '';
        $category_name_parent = '';

        if($categories && isset($categories[0]) && $categories[0]) {
            $firstCategory = $categories[0];
            $parentCategory = $this->_getCategoryMostParent($firstCategory->getCategory());

            $category_name = $firstCategory->getCategory()->getName();
            if($parentCategory) {
                $category_name_parent = $parentCategory->getName();
            }
        }

        $title = $Product->seo_title;
        if (isset($Product->seo_title) && $Product->seo_title != '')
        {
            
            $title = preg_replace(self::PRODUCT_NAME_PATTERN, '${1}' . $Product->getName() . '${4}', $title);
            $title = preg_replace(self::PRODUCT_PRICE01_PATTERN, is_null($Product->getPrice01Min()) ? '' : '${1}' . sprintf('%s', number_format($Product->getPrice01IncTaxMin())) . '${4}', $title);
            $title = preg_replace(self::PRODUCT_PRICE02_PATTERN, sprintf('%s', '${1}' . number_format($Product->getPrice02IncTaxMin())) . '${4}', $title);
            $title = preg_replace(self::CATEGORY_NAME_PATTERN, $category_name ? '${1}'.$category_name.'${4}' : '', $title);
            $title = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $category_name_parent ? '${1}'.$category_name_parent.'${4}' : '', $title);
        }
        
        $og_title = $Product->og_title;
        if (isset($Product->og_title) && $Product->og_title != '')
        {
            $og_title = preg_replace(self::PRODUCT_NAME_PATTERN, '${1}' . $Product->getName() . '${4}', $og_title);
            $og_title = preg_replace(self::PRODUCT_PRICE01_PATTERN, is_null($Product->getPrice01Min()) ? '' : '${1}' . sprintf('%s', number_format($Product->getPrice01IncTaxMin())) . '${4}', $og_title);
            $og_title = preg_replace(self::PRODUCT_PRICE02_PATTERN, sprintf('%s', '${1}' . number_format($Product->getPrice02IncTaxMin())) . '${4}', $og_title);
            $og_title = preg_replace(self::CATEGORY_NAME_PATTERN, $category_name ? '${1}'.$category_name.'${4}' : '', $og_title);
            $og_title = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $category_name_parent ? '${1}'.$category_name_parent.'${4}' : '', $og_title);
        }

        $description = $Product->seo_description;
        if (isset($Product->seo_description) && $Product->seo_description != '')
        {
            $description = preg_replace(self::PRODUCT_NAME_PATTERN, '${1}' . $Product->getName() . '${4}', $description);
            $description = preg_replace(self::PRODUCT_PRICE01_PATTERN, is_null($Product->getPrice01Min()) ? '' : '${1}' . sprintf('%s', number_format($Product->getPrice01IncTaxMin())) . '${4}', $description);
            $description = preg_replace(self::PRODUCT_PRICE02_PATTERN, sprintf('%s', '${1}' . number_format($Product->getPrice02IncTaxMin())) . '${4}', $description);
            $description = preg_replace(self::CATEGORY_NAME_PATTERN, $category_name ? '${1}'.$category_name.'${4}' : '', $description);
            $description = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $category_name_parent ? '${1}'.$category_name_parent.'${4}' : '', $description);
        }

        $og_description = $Product->og_description;
        if (isset($Product->og_description) && $Product->og_description != '')
        {
            $og_description = preg_replace(self::PRODUCT_NAME_PATTERN, '${1}' . $Product->getName() . '${4}', $og_description);
            $og_description = preg_replace(self::PRODUCT_PRICE01_PATTERN, is_null($Product->getPrice01Min()) ? '' : '${1}' . sprintf('%s', number_format($Product->getPrice01IncTaxMin())) . '${4}', $og_description);
            $og_description = preg_replace(self::PRODUCT_PRICE02_PATTERN, sprintf('%s', '${1}' . number_format($Product->getPrice02IncTaxMin())) . '${4}', $og_description);
            $og_description = preg_replace(self::CATEGORY_NAME_PATTERN, $category_name ? '${1}'.$category_name.'${4}' : '', $og_description);
            $og_description = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $category_name_parent ? '${1}'.$category_name_parent.'${4}' : '', $og_description);
        }

        $keyword = $Product->seo_keywords;
        
        if (isset($Product->seo_keywords) && $Product->seo_keywords != '')
        {
            $keyword = preg_replace(self::PRODUCT_NAME_PATTERN, '${1}' . $Product->getName() . '${4}', $keyword);
            $keyword = preg_replace(self::PRODUCT_PRICE01_PATTERN, is_null($Product->getPrice01Min()) ? '' : '${1}' . sprintf('%s', number_format($Product->getPrice01IncTaxMin())) . '${4}', $keyword);
            $keyword = preg_replace(self::PRODUCT_PRICE02_PATTERN, sprintf('%s', '${1}' . number_format($Product->getPrice02IncTaxMin())) . '${4}', $keyword);
            $keyword = preg_replace(self::CATEGORY_NAME_PATTERN, $category_name ? '${1}'.$category_name.'${4}' : '', $keyword);
            $keyword = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $category_name_parent ? '${1}'.$category_name_parent.'${4}' : '', $keyword);
        }

        $Product->seo_title = $title;
        $Product->og_title = $og_title;

        $Product->seo_description = $description;
        $Product->og_description = $og_description;

        $Product->seo_keywords = $keyword;

        return $Product;
    }

    private function readyPlaceholderCategory(Category $Category)
    {
        $CategoryMostParent = $this->_getCategoryMostParent($Category);
        $categoryMostParentName = '';
        if(!is_null($CategoryMostParent)) {
            $categoryMostParentName = $CategoryMostParent->getName();
        }
        $title = $Category->seo_title;
        if (isset($Category->seo_title) && $Category->seo_title != '')
        {
            $title = preg_replace(self::CATEGORY_NAME_PATTERN, $Category->getName(), $title);
            $title = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $categoryMostParentName ? '${1}'.$categoryMostParentName.'${4}' : '', $title);
        }

        $description = $Category->seo_description;
        if (isset($Category->seo_description) && $Category->seo_description != '')
        {
            $description = preg_replace(self::CATEGORY_NAME_PATTERN, $Category->getName(), $description);
            $description = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $categoryMostParentName ? '${1}'.$categoryMostParentName.'${4}' : '', $description);
        }

        $keyword = $Category->seo_keywords;
        if (isset($Category->seo_keywords) && $Category->seo_keywords != '')
        {
            $keyword = preg_replace(self::CATEGORY_NAME_PATTERN, $Category->getName(), $keyword);
            $keyword = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $categoryMostParentName ? '${1}'.$categoryMostParentName.'${4}' : '', $keyword);
        }

        $Category->seo_title = $title;

        $Category->seo_description = $description;

        $Category->seo_keywords = $keyword;
        
        $og_title = $Category->og_title;
        if (isset($Category->og_title) && $Category->og_title != '')
        {
            $og_title = preg_replace(self::CATEGORY_NAME_PATTERN, $Category->getName(), $og_title);
            $og_title = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $categoryMostParentName ? '${1}'.$categoryMostParentName.'${4}' : '', $og_title);
        }

        $og_description = $Category->og_description;
        if (isset($Category->og_description) && $Category->og_description != '')
        {
            $og_description = preg_replace(self::CATEGORY_NAME_PATTERN, $Category->getName(), $og_description);
            $og_description = preg_replace(self::CATEGORY_PARENTNAME_PATTERN, $categoryMostParentName ? '${1}'.$categoryMostParentName.'${4}' : '', $og_description);
        }

        $Category->og_title = $og_title;

        $Category->og_description = $og_description;

        return $Category;
    }

    public function onAdminProductCategoryTwig(TemplateEvent $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        $twig = $this->container->get('twig');
        $source = $event->getSource();

        $view_src = '';

        $search_code_1 = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_3.twig');
        $replace_code_1 = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_5.twig');

        if (strpos($source, str_replace("\r", '', $search_code_1->getCode())))
        {
            $view_src = str_replace(str_replace("\r", '', $search_code_1->getCode()), $replace_code_1->getCode(), $source);
        }

        $search_code_2 = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_4.twig');
        $replace_code_2 = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/admin/seoallone_search_form_6.twig');

        if (strpos($source, str_replace("\r", '', $search_code_2->getCode())))
        {
            $view_src = str_replace(str_replace("\r", '', $search_code_2->getCode()), $replace_code_2->getCode(), $view_src);
        }


        $event->setSource($view_src);
    }

    public function onIndexTwig(TemplateEvent $event)
    {
        $PageHomepage = $this->pageRepository->findOneBy(array('url' => 'homepage'));
        $parameters = $event->getParameters();
        if (!$PageHomepage)
        {
            return;
        }


        $Config = $this->configRepository->findOneBy([]);
        $event->setParameter('SEOAllOneConfig', $Config);
        $parameters['SEOAllOneConfig'] = $Config;
		$default_seo_parameter = $this->seoAllOneDefaultRepository->findOneby(array('Page' => $PageHomepage));
		if (!$default_seo_parameter) {
            //タグ設定されていない時
			return;
        }

        if (is_null($Config))
        {
            return;
        }

        
        $parameters['seoallone_title'] = $default_seo_parameter->getTitle();
        $parameters['seoallone_description'] = $default_seo_parameter->getDescription();
        $parameters['seoallone_keyword'] = $default_seo_parameter->getKeyword();
        $parameters['seoallone_author'] = $default_seo_parameter->getAuthor();
        $parameters['seoallone_canonical'] = $default_seo_parameter->getCanonical();

        $parameters['seoallone_og_site_name'] = $default_seo_parameter->getOGSiteName();
        $parameters['seoallone_og_title'] = $default_seo_parameter->getOGTitle();
        $parameters['seoallone_og_description'] = $default_seo_parameter->getOGDescription();
        $parameters['seoallone_og_url'] = $default_seo_parameter->getOGUrl();
        $parameters['seoallone_og_type'] = $default_seo_parameter->getOGType();
        $parameters['seoallone_og_image'] = $default_seo_parameter->getOGImage();

        $event->setParameters($parameters);

        $meta_tags = $PageHomepage->getMetaTags();
       
        if ($meta_tags) {
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:title"[^>]+(\/)?>/m', '', $meta_tags);
            if ($parameters['seoallone_og_description'] || $parameters['seoallone_description'] || $PageHomepage->getDescription()) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:description"[^>]+(\/)?>/m', '', $meta_tags);
            }
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:url"[^>]+(\/)?>/m', '', $meta_tags);
            if ($parameters['seoallone_og_image']) {
                $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:image"[^>]+(\/)?>/m', '', $meta_tags);
            }
            $meta_tags = preg_replace('/(<(\s)*)meta(\s)*(name|property)="og:type"[^>]+(\/)?>/m', '', $meta_tags);
            $meta_tags = str_replace("\r\n\r\n", "", $meta_tags);
            $meta_tags = str_replace("\n\r\n\r", "", $meta_tags);
        }
        $PageHomepage->setMetaTags($meta_tags);
    }

    public function onFrontProductDetailTwig(TemplateEvent $event)
    {
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getMasterRequest();
        $parameters = $event->getParameters();
        
        $Config = $this->configRepository->findOneBy([]);
        $event->setParameter('SEOAllOneConfig', $Config);
        $parameters['SEOAllOneConfig'] = $Config;

        $seo_title = !empty($parameters['title'])? $parameters['title'] : '';
        $seo_subtitle = !empty($parameters['subtitle'])? $parameters['subtitle'] : '';
        $request->attributes->set('seo_title', $seo_title);
        $request->attributes->set('seo_subtitle', $seo_subtitle);
        
        if ($Config->getSnsFlg() == 1) {
            $event->addSnippet('@SEOAllOne/social_button.twig');

            
            $parameters['facebook_flg'] = $Config->getFacebookFlg();
            $parameters['twitter_flg'] = $Config->getTwitterFlg();
            $parameters['line_flg'] = $Config->getLineFlg();

            $event->setParameters($parameters);
        }

        if ($Config->getRichSnippetFlg() == 1)
        {
            $event->addSnippet('@SEOAllOne/json-ld.twig');
        }

		// remove default json schema
		$twig = $this->container->get('twig');
        $search_code = $twig->getLoader()->getSourceContext('SEOAllOne/Resource/template/json-ld-search.twig');
        $replace_code = "";
        $source = $event->getSource();			
        if (strpos($source, str_replace("\r", '', $search_code->getCode())))
        {
            $view_src = str_replace(str_replace("\r", '', $search_code->getCode()), $replace_code, $source);
            $event->setSource($view_src);
        }
    }

    private function readyPagination(&$parameters)
    {
        // $parameters = $event->getParameters();
        $paginationData = $parameters['pagination']->getPaginationData();
        $link_prev = NULL;
        $link_next = NULL;

        $Config = $this->configRepository->findOneBy([]);
        if ($Config->getPaginationFlg() == 1)
        {
            $requestStack = $this->container->get('request_stack');
            $request = $requestStack->getMasterRequest();
            if ($request->getMethod() === 'GET') {
                $all = $request->query->all();
                if (array_key_exists('pageno', $all) && $all['pageno'] == 0) {
                    $all['pageno'] = 1;
                }
            }
            if ($paginationData['numItemsPerPage'] >= $paginationData['totalCount'])
            {
                $link_next = NULL;
                $link_prev = NULL;
            }
            else
            {
                if ($paginationData['current'] == $paginationData['first'])
                {
                    $link_prev = NULL;
                    $all['pageno'] =$paginationData['next'];
                    $link_next = '?' . http_build_query($all);
                }
                elseif ($paginationData['current'] == $paginationData['last'])
                {
                    $link_next = NULL;
                    $all['pageno'] =$paginationData['previous'];
                    $link_prev = '?' . http_build_query($all);
                }
                else
                {
                    $all['pageno'] =$paginationData['previous'];
                    $link_prev = '?' . http_build_query($all);
                    $all['pageno'] =$paginationData['next'];
                    $link_next = '?' . http_build_query($all);
                }
            }
        }
        
        $parameters['seo_link_prev'] = $link_prev;
        $parameters['seo_link_next'] = $link_next;
        // $event->setParameters($parameters);
    }

    public function onFrontProductIndexInitialize(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        $SEOProducts = $this->seoAllOneProductRepository->findAll();
        $ids = [];
        if ($SEOProducts)
        {
            foreach($SEOProducts as $SEOProduct)
            {
                if ($SEOProduct->getRedirectUrl() != '')
                {
                    $ids[] = $SEOProduct->getProduct()->getId();
                }
            }
        }

        if ($ids)
        {
            $qb = $event->getArgument('qb');
            foreach($ids as $id)
            {
                $qb->andWhere('p.id != '.$id);
            }
            $event->setArgument('qb', $qb);
        }
    }

    public function onAdminProductDeleteComplete(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }
    }

    public function onAdminContentPageDeleteComplete(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }
    }

    public function onAdminProductCategoryDeleteComplete(EventArgs $event)
    {
        $Config = $this->configRepository->findOneBy([]);
        if ($Config->getSitemapFlg() == 1)
        {
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
        }
    }

    private function _getCategoryMostParent(Category $Category) {
        if($Category->getHierarchy() == 1) {
            return NULL;
        }

        $parents = $Category->getParents();
        if(isset($parents[0]) && trim($parents[0]->getName()) != trim($Category->getName())) {
            return $parents[0];
        }

        return NULL;
    }
	
	public function onAdminStorePluginIndexTwig(TemplateEvent $event)
	{
		$em = $this->container->get('doctrine.orm.entity_manager');
		$conn = $em->getConnection();
		
		$driver = '';
		$conn = $em->getConnection();
		$params = $conn->getParams();
		if (isset($params['driver'])){
			$driver = $params['driver'];
		}
		
		// used in case upgrade plugin - make `shop_name_top_flg` column in case does not exist
		if ($driver == 'pdo_mysql') {
			// shop_name_top_flg
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'shop_name_top_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE `plg_seoallone_config` ADD COLUMN `shop_name_top_flg` INT(1) DEFAULT '1' AFTER `line_flg`");
			}
			
			// global_id_flg
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'global_id_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_flg TINYINT NOT NULL DEFAULT 0");					
			}
			
			// global_id_type
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT DEFAULT 3");				
			}
			
			// valid_price_flg
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'valid_price_flg'");
			$cnt = $stmt->fetchAll();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_flg TINYINT NOT NULL DEFAULT 0");				
			}			
			
			// valid_price_month
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_config` LIKE 'valid_price_month'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT DEFAULT 0");			
			}
			
			// global_id_type for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id_type INT DEFAULT 3");				
			}
			
			// global_id for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'global_id'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id VARCHAR(255) DEFAULT NULL");				
			}			
			
			// valid_price_month for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'valid_price_month'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN valid_price_month INT DEFAULT 0");			
			}		
			
			// valid_price_flg for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'updated_flg'");
			$cnt = $stmt->fetchAll();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN updated_flg TINYINT NOT NULL DEFAULT 0");				
			}		
		} elseif ($driver == 'pdo_pgsql') {
			// shop_name_top_flg
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_config' and column_name='shop_name_top_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN shop_name_top_flg boolean NOT NULL DEFAULT TRUE");
			}
			
			// global_id_flg
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_config' and column_name='global_id_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_flg boolean NOT NULL DEFAULT FALSE");					
			}
			
			// global_id_type
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_config' and column_name='global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT DEFAULT 3");				
			}
			
			// valid_price_flg
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_config' and column_name='valid_price_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_flg boolean NOT NULL DEFAULT FALSE");				
			}			
			
			// valid_price_month
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_config' and column_name='valid_price_month'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT DEFAULT 0");			
			}		
			
			// global_id_type for product
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_product' and column_name='global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id_type INT DEFAULT 3");				
			}
			
			// global_id for product
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_product' and column_name='global_id'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id VARCHAR(255) DEFAULT NULL");				
			}			
			
			// valid_price_month
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_product' and column_name='valid_price_month'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN valid_price_month INT DEFAULT 0");			
			}	
			
			// updated_flg
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_product' and column_name='updated_flg'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN updated_flg boolean NOT NULL DEFAULT FALSE");				
			}		
		} elseif ($driver == 'pdo_sqlite') {
			// shop_name_top_flg
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_config)");
			$res = $stmt->fetchAll();
			if ($res) {
				$shop_name_top_flg_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'shop_name_top_flg'){
						$shop_name_top_flg_exist = TRUE;
						break;
					}
				}
				if (!$shop_name_top_flg_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN shop_name_top_flg TINYINT NOT NULL DEFAULT 1");
				}				
			}		
			
			// global_id_flg
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_config)");
			$res = $stmt->fetchAll();
			if ($res) {
				$global_id_flg_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'global_id_flg'){
						$global_id_flg_exist = TRUE;
						break;
					}
				}
				if (!$global_id_flg_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_flg TINYINT NOT NULL DEFAULT 0");
				}				
			}
			
			// global_id_type
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_config)");
			$res = $stmt->fetchAll();
			if ($res) {
				$global_id_type_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'global_id_type'){
						$global_id_type_exist = TRUE;
						break;
					}
				}
				if (!$global_id_type_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT DEFAULT 3");
				}				
			}
			
			// valid_price_flg
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_config)");
			$res = $stmt->fetchAll();
			if ($res) {
				$valid_price_flg_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'valid_price_flg'){
						$valid_price_flg_exist = TRUE;
						break;
					}
				}
				if (!$valid_price_flg_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_flg TINYINT NOT NULL DEFAULT 0");
				}				
			}			
			
			// valid_price_month
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_config)");
			$res = $stmt->fetchAll();
			if ($res) {
				$valid_price_month_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'valid_price_month'){
						$valid_price_month_exist = TRUE;
						break;
					}
				}
				if (!$valid_price_month_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT DEFAULT 0");
				}				
			}	
			
			// global_id_type for product
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_product)");
			$res = $stmt->fetchAll();
			if ($res) {
				$global_id_type_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'global_id_type'){
						$global_id_type_exist = TRUE;
						break;
					}
				}
				if (!$global_id_type_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id_type INT DEFAULT 3");
				}				
			}		
			
			// global_id for product
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_product)");
			$res = $stmt->fetchAll();
			if ($res) {
				$global_id_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'global_id'){
						$global_id_exist = TRUE;
						break;
					}
				}
				if (!$global_id_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id VARCHAR(255) DEFAULT NULL");
				}				
			}				
			
			// valid_price_month for product
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_product)");
			$res = $stmt->fetchAll();
			if ($res) {
				$valid_price_month_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'valid_price_month'){
						$valid_price_month_exist = TRUE;
						break;
					}
				}
				if (!$valid_price_month_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN valid_price_month INT DEFAULT 0");
				}				
			}	
			
			// valid_price_flg
			$stmt = $conn->executeQuery("PRAGMA table_info(plg_seoallone_product)");
			$res = $stmt->fetchAll();
			if ($res) {
				$updated_flg_exist = FALSE;
				foreach ($res as $item) {
					if ($item['name'] == 'updated_flg'){
						$updated_flg_exist = TRUE;
						break;
					}
				}
				if (!$updated_flg_exist) {
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN updated_flg TINYINT NOT NULL DEFAULT 0");
				}				
			}	
		}
	}
}
