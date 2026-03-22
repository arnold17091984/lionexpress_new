<?php

namespace Plugin\SEOAllOne\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\SEOAllOne\Form\Type\Admin\ConfigType;
use Plugin\SEOAllOne\Repository\ConfigRepository;
use Plugin\SEOAllOne\Util\Util;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Eccube\Common\EccubeConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Eccube\Repository\ProductRepository;
use Plugin\SEOAllOne\Entity\SEOAllOneProduct;
use Plugin\SEOAllOne\Repository\SEOAllOneProductRepository;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;
    protected $container;
    protected $eccubeConfig;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository, ContainerInterface $container, EccubeConfig $eccubeConfig,
		ProductRepository $productRepository)
    {
        $this->configRepository = $configRepository;
        $this->container = $container;
        $this->eccubeConfig = $eccubeConfig;
		$this->productRepository = $productRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/config", name="seo_all_one_admin_config")
     * @Template("@SEOAllOne/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $sitemapFlag = $Config->getSitemapFlg();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            if ($Config->getSnsFlg() == 0)
            {
                $Config->setFacebookFlg(FALSE);
                $Config->setTwitterFlg(FALSE);
                $Config->setLineFlg(FALSE);
            }
			if(!$Config->getValidPriceMonth()) {
				$Config->setValidPriceMonth(0);
			}
            if($Config->getSitemapFlg() != $sitemapFlag) {
                if($Config->getSitemapFlg() == 0) {
                    $path = $this->eccubeConfig->get('kernel.project_dir') . '/sitemap.xml';
                    if(is_file($path))  
                    {
                        // delete file
                        @chmod($path, 0777);
                        @unlink($path);
                    }
                } else {
                    $Util = new Util($this->container);
                    $Util->generateSitemap($this->container, $this->eccubeConfig);
                }
            }
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);
			
			// update products
			/*
			if($Config->getValidPriceFlg()) {
				$em = $this->container->get('doctrine.orm.entity_manager');
				$conn = $em->getConnection();
				
				$configGlobalIdType = (int)$Config->getGlobalIdType();
				$configValidPriceMonth = (int)$Config->getValidPriceMonth();
				
				$query = "SELECT dtb_product.id, plg_seoallone_product.product_id, plg_seoallone_product.global_id_type, plg_seoallone_product.global_id, plg_seoallone_product.valid_price_month ";
				$query.= "FROM dtb_product left join plg_seoallone_product on dtb_product.id=plg_seoallone_product.product_id ";
				$res = $conn->fetchAll($query);
				if ($res) {
					foreach ($res as $item) {
						if (!$item['product_id']){
							$SeoalloneProduct = new SEOAllOneProduct();
							if (!$SeoalloneProduct->getValidPriceMonth()) {
								$Product = $this->productRepository->findOneBy(['id' => $item['id']]);
								$SeoalloneProduct->setProduct($Product);
								$SeoalloneProduct->setGlobalIdType($configGlobalIdType);
								$SeoalloneProduct->setValidPriceMonth($configValidPriceMonth);
								$SeoalloneProduct->setNoindexFlg(0);
								$SeoalloneProduct->setDelFlg($configValidPriceMonth);
								$em->persist($SeoalloneProduct);
								$em->flush($SeoalloneProduct);
							}
						}						
					}
				}
			}
			*/
			
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('seo_all_one_admin_config');
        }

        return [
            'form' => $form->createView(),
            'Config'    => $Config,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/seoallone/manual", name="seoallone_admin_manual")
     * @Template("@SEOAllOne/admin/manual.twig")
     */
    public function manual(Request $request)
    {
        return [];
    }
}
