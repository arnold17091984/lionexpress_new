<?php

namespace Plugin\SEOAllOne\Controller\Admin;
use Eccube\Controller\AbstractController;
use Eccube\Repository\PageRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CategoryRepository;
use Plugin\SEOAllOne\Form\Type\Admin\SitemapConfigType;
use Plugin\SEOAllOne\Entity\SitemapConfig;
use Plugin\SEOAllOne\Repository\SitemapConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Plugin\SEOAllOne\Repository\ConfigRepository;
use Plugin\SEOAllOne\Util\Util;
use Psr\Container\ContainerInterface;

class SitemapConfigController extends AbstractController 
{
    /**
     * @var SitemapConfigRepository
     */
    protected $sitemapConfigRepository;

    /**
     * SitemapConfigController constructor.
     *
     * @param SitemapConfigRepository $sitemapConfigRepository
     */
    public function __construct(ContainerInterface $container, SitemapConfigRepository $sitemapConfigRepository, PageRepository $pageRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository, ConfigRepository $configRepository)
    {
        $this->container = $container;
        $this->sitemapConfigRepository = $sitemapConfigRepository;
        $this->pageRepository = $pageRepository;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->configRepository = $configRepository;
        $this->Config = $this->configRepository->get();
        $this->util = new Util($this->container);
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/sitemap_config", name="seoallone_admin_sitemap_config")
     * @Template("@SEOAllOne/admin/sitemap_config.twig")
     */
    public function index(Request $request)
    {
        if($this->Config->getSitemapFlg() == 0) {
            // return $this->redirectToRoute('seo_all_one_admin_config');
            $this->addWarning('この設定は 「メイン設定」で無効にされています。', 'admin');
        }
        $exclude_sitemap_pages = $this->eccubeConfig['EXCLUDE_SITEMAP_PAGES'];
        $SitemapConfig = $this->sitemapConfigRepository->findAll();

        $all_pages = $this->pageRepository->findAll();
        $disp_page = array();
        foreach($all_pages as $page)
        {
            if (strpos($page->getMetaRobots(), 'noindex') !== false) continue;
            if (!in_array($page->getUrl(), $exclude_sitemap_pages))
            {
                $sitemapConfig = $this->sitemapConfigRepository->findOneBy(array('Page' => $page));

                if ($sitemapConfig)
                {
                    $disp_page[] = array(
                        'id'            => $page->getId(),
                        'name'     => $page->getName(),
                        'url_name'      => $page->getUrl(),
                        'changefreq'    => $sitemapConfig->getChangeFreq(),
                        'priority'      => $sitemapConfig->getPriority()
                    );
                }
                elseif($page->getUrl() == 'homepage')
                {
                    $disp_page[] = array(
                        'id'    => $page->getId(),
                        'name'  => $page->getName(),
                        'url_name'  => $page->getUrl(),
                        'changefreq'    => $this->eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_CHANGE_FREQ'],
                        'priority'  => $this->eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_PRIORITY'],
                    );
                }
                elseif($page->getUrl() == 'product_list')
                {
                    $disp_page[] = array(
                        'id'    => $page->getId(),
                        'name'  => $page->getName(),
                        'url_name'  => $page->getUrl(),
                        'changefreq'    => $this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_CHANGE_FREQ'],
                        'priority'  => $this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_PRIORITY']
                    );
                }
                elseif($page->getUrl() == 'product_detail')
                {
                    $disp_page[] = array(
                        'id'    => $page->getId(),
                        'name'  => $page->getName(),
                        'url_name'  => $page->getUrl(),
                        'changefreq'    => $this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_CHANGE_FREQ'],
                        'priority'  => $this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_PRIORITY']
                    );
                }
                else
                {
                    $disp_page[] = array(
                        'id'            => $page->getId(),
                        'name'     => $page->getName(),
                        'url_name'      => $page->getUrl(),
                        'changefreq'    => $this->eccubeConfig['DEFAULT_SITEMAP_CHANGE_FREQ'],
                        'priority'      => $this->eccubeConfig['DEFAULT_SITEMAP_PRIORITY']
                    );
                }
            }
        }

        return [
            'pages' => $disp_page,
            'Config'    => $this->Config
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/sitemap_config/{id}/edit", requirements={"id" = "\d+"}, name="seoallone_admin_sitemap_config_edit")
     * @Template("@SEOAllOne/admin/sitemap_config_edit.twig")
     */
    public function edit(Request $request, $id)
    {
        if($this->Config->getSitemapFlg() == 0) {
            return $this->redirectToRoute('seo_all_one_admin_config');
        }
        $Page = $this->pageRepository->find($id);
        $sitemapConfig = $this->sitemapConfigRepository->findOneBy(array('Page' => $Page));

        if (!$sitemapConfig)
        {
            $sitemapConfig = new SitemapConfig();
            $sitemapConfig->setPage($Page);
            if ($Page->getUrl() == 'homepage')
            {
                $sitemapConfig->setChangeFreq($this->eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_CHANGE_FREQ']);
                $sitemapConfig->setPriority($this->eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_PRIORITY']);
            }
            elseif ($Page->getUrl() == 'product_list')
            {
                $sitemapConfig->setChangeFreq($this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_CHANGE_FREQ']);
                $sitemapConfig->setPriority($this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_PRIORITY']);
            }
            elseif ($Page->getUrl() == 'product_detail')
            {
                $sitemapConfig->setChangeFreq($this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_CHANGE_FREQ']);
                $sitemapConfig->setPriority($this->eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_PRIORITY']);
            }
            else
            {
                $sitemapConfig->setChangeFreq($this->eccubeConfig['DEFAULT_SITEMAP_CHANGE_FREQ']);
                $sitemapConfig->setPriority($this->eccubeConfig['DEFAULT_SITEMAP_PRIORITY']);
            }
        }

        $form = $this->createForm(SitemapConfigType::class, $sitemapConfig);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sitemapConfig = $form->getData();
            $sitemapConfig->setUrlName($sitemapConfig->getPage()->getUrl());

            $this->entityManager->persist($sitemapConfig);
            $this->entityManager->flush($sitemapConfig);

            $this->util->generateSitemap($this->container, $this->eccubeConfig);

            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('seoallone_admin_sitemap_config_edit', ['id' => $Page->getId()]);
        }

        return [
            'Page'  => $Page,
            'form' => $form->createView(),
        ];
    }
}

?>