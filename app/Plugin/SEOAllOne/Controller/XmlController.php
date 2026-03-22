<?php

namespace Plugin\SEOAllOne\Controller;
use Eccube\Controller\AbstractController;
use Eccube\Repository\PageRepository;
use Eccube\Repository\ProductRepository;
use Eccube\Repository\CategoryRepository;
use Plugin\SEOAllOne\Form\Type\Admin\SitemapConfigType;
use Plugin\SEOAllOne\Entity\Config;
use Plugin\SEOAllOne\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Filesystem\Filesystem;

use Plugin\SEOAllOne\Util\Util;

class XmlController extends AbstractController 
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * XmlController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->util = new Util();
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/xml", name="seoallone_xml")
     * @Template("@SEOAllOne/admin/sitemap_message.twig")
     */
    public function xml(Request $request)
    {
        $Config = $this->configRepository->get();

        if ($Config->getSitemapFlg() == 1)
        {
            $start = date("Y-m-d H:i:s")." START \n";
            $this->util->generateSitemap($this->container, $this->eccubeConfig);
            $end = date("Y-m-d H:i:s")." END \n";
            return [
                'start' => $start,
                'end' => $end,
            ];
        }
        else {
            return [
                'msg'   => 'この設定は 「メイン設定」で無効にされています。'
            ];
        }
    }
}