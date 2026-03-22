<?php 

namespace Plugin\SEOAllOne\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Eccube\Repository\ProductRepository;
use Eccube\Repository\PageRepository;
use Eccube\Repository\CategoryRepository;
use Plugin\SEOAllOne\Repository\SitemapConfigRepository;

use Eccube\Entity\Product;
use Eccube\Entity\Page;
use Eccube\Entity\Category;
use Plugin\SEOAllOne\Entity\SitemapConfig;

use Eccube\Common\EccubeConfig;

class Util {

    /**
     * @var ProductRepository
     */
    protected $productRepository;

	/**
	 * @var PageRepository
	 */
	protected $pageRepository;

	/**
	 * @var CategoryRepository
	 */
	protected $categoryRepository;

	/**
     * @var SitemapConfigRepository
     */
	protected $sitemapConfigRepository;
	


    /**
     * @param Environment $twig
     * @param MobileDetector $detector
     * @param EccubeConfig $eccubeConfig
     * @param RequestStack $requestStack
     * @param PageRepository $pageRepository
     * @param LayoutRepository $layoutRepository
     * @param ConfigRepository $configRepository
     * @param BaseInfoRepository $baseInfoRepository
     * @param ContainerInterface $container
     */
    public function __construct(
								ContainerInterface $container
								)
    {
        $this->container = $container;
    }

    public function generateSitemap(ContainerInterface $container, EccubeConfig $eccubeConfig)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $productRepository = $em->getRepository(Product::class);
        $pageRepository = $em->getRepository(Page::class);
        $categoryRepository = $em->getRepository(Category::class);
        $sitemapConfigRepository = $em->getRepository(SitemapConfig::class);

        $router = $container->get('router');

        $Pages = $pageRepository->findAll();
        $Categories = $categoryRepository->findAll();

        $qb = $productRepository->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('Plugin\SEOAllOne\Entity\SEOAllOneProduct', 'sp', \Doctrine\ORM\Query\Expr\Join::WITH, 'sp.Product = p.id')
            ->where('sp IS NULL')
            ->orWhere('sp.noindex_flg = 0')
            ->andWhere('p.Status = 1');
        $Details = $qb->getQuery()->getResult();

        $Items = array();
        $exclude_sitemap_pages = $eccubeConfig['EXCLUDE_SITEMAP_PAGES'];
        foreach($Pages as $page)
        {
			try {
				if (strpos($page->getMetaRobots(), 'noindex') !== false) continue;

				if (in_array($page->getUrl(), $exclude_sitemap_pages)) continue;

				$sitemapConfig = $sitemapConfigRepository->findOneBy(array('Page' => $page));
				if ($sitemapConfig)
				{
					$change_freq = $sitemapConfig->getChangeFreq();
					$priority = $sitemapConfig->getPriority();
				}
				elseif ($page->getUrl() == 'homepage')
				{
					$change_freq = $eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_CHANGE_FREQ'];
					$priority = $eccubeConfig['DEFAULT_SITEMAP_HOMEPAGE_PRIORITY'];
				}
				elseif ($page->getUrl() == 'product_list')
				{
					$change_freq = $eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_CHANGE_FREQ'];
					$priority = $eccubeConfig['DEFAULT_SITEMAP_PRODUCT_LIST_PRIORITY'];
				}
				elseif ($page->getUrl() == 'product_detail')
				{
					$change_freq = $eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_CHANGE_FREQ'];
					$priority = $eccubeConfig['DEFAULT_SITEMAP_PRODUCT_DETAIL_PRIORITY'];
				}
				else
				{
					$change_freq = $eccubeConfig['DEFAULT_SITEMAP_CHANGE_FREQ'];
					$priority = $eccubeConfig['DEFAULT_SITEMAP_PRIORITY'];
				}

				if ($page->getUrl() == 'homepage') {
					$Item = array();
					$Item['loc'] = $router->generate($page->getUrl(), [], UrlGeneratorInterface::ABSOLUTE_URL);
					$Item['lastmod'] = $this->dateToString($page->getUpdateDate());
					$Item['changefreq'] = $change_freq;
					$Item['priority'] = $priority;
					$Items[] = $Item;
				}
				elseif ($page->getUrl() == 'product_list') {
					foreach ($Categories as $category) {
						$Item = array();
						$Item['loc'] = $router->generate('product_list', ['category_id' => $category->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
						$Item['lastmod'] = $this->dateToString($category->getUpdateDate());
						$Item['changefreq'] = $change_freq;
						$Item['priority'] = $priority;
						$Items[] = $Item;
					}
				}
				elseif ($page->getUrl() == 'product_detail') {
					foreach ($Details as $detail) {
						$Item = array();
						$Item['loc'] = $router->generate('product_detail', ['id' => $detail->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
						$Item['lastmod'] = $this->dateToString($detail->getUpdateDate());
						$Item['changefreq'] = $change_freq;
						$Item['priority'] = $priority;
						$Items[] = $Item;
					}
				}
				elseif ($page->getEditType() === 0) {
					$Item = array();
					$Item['loc'] = $router->generate($eccubeConfig['eccube_user_data_route'], ['route' => $page->getUrl()], UrlGeneratorInterface::ABSOLUTE_URL);
					$Item['lastmod'] = $this->dateToString($page->getUpdateDate());
					$Item['changefreq'] = $change_freq;
					$Item['priority'] = $priority;
					$Items[] = $Item;
				}
				else {
					$Item = array();
					$Item['loc'] = $router->generate($page->getUrl(), [], UrlGeneratorInterface::ABSOLUTE_URL);
					$Item['lastmod'] = $this->dateToString($page->getUpdateDate());
					$Item['changefreq'] = $change_freq;
					$Item['priority'] = $priority;
					$Items[] = $Item;
				}
			} catch (\Exception $exception) {}
        }

        $data = $this->renderView(
            $container,
            'SEOAllOne/Resource/template/admin/sitemap.twig',
            [
                'Items' => $Items
            ]
        );

        $file = new Filesystem();
        $path = $eccubeConfig->get('kernel.project_dir') . '/sitemap.xml';
        $file->dumpFile($path, $data);
    }

    private function dateToString($date)
    {
        return date_format($date, 'Y-m-d\TH:i:s+09:00');
    }

    public function renderView($container, $view, array $parameters = [])
    {
        if ($container->has('templating')) {
            return $container->get('templating')->render($view, $parameters);
        }

        if (!$container->has('twig')) {
            throw new \LogicException('You can not use the "renderView" method if the Templating Component or the Twig Bundle are not available. Try running "composer require symfony/twig-bundle".');
        }

        return $container->get('twig')->render($view, $parameters);
    }
}

?>