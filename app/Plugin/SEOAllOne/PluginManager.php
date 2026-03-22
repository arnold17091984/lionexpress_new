<?php

namespace Plugin\SEOAllOne;

use Eccube\Plugin\AbstractPluginManager;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Layout;
use Eccube\Entity\Page;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Plugin\SEOAllOne\Entity\SEOAllOneDefault;

use Plugin\SEOAllOne\Entity\Config;
use Plugin\SEOAllOne\Util\Util;

class PluginManager extends AbstractPluginManager
{
    private $og_tags_filename = 'seoallone_og_tags';
    private $og_tags_blockname = 'seoallone_og_tags_block';

    private $breadcrumb_filename = 'seoallone_breadcrumb';
    private $breadcrumb_blockname = 'seoallone_breadcrumb_block';

    private $default_frame_filename = 'default_frame';

    public function install(array $meta, ContainerInterface $container)
    {
	}

    public function enable(array $meta, ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $SEOAllOneConfigRepository = $em->getRepository(Config::class);

        $this->registerOgBlockData($container);
        $this->registerBreadcrumbBlockData($container);
        $this->copyBlock($container);

        $this->initConfigData($container);
        $this->initDefaultMetaCategoryData($container);
        $this->initDefaultMetaProductData($container);

        $SEOAllOneConfig = $SEOAllOneConfigRepository->findOneBy([]);
        $eccubeConfig = $container->get('Eccube\Common\EccubeConfig');
        $Util = new Util($container);
        if($SEOAllOneConfig->getSitemapFlg() == 1) {
            $Util->generateSitemap($container, $eccubeConfig);
        }
	}

    public function disable(array $meta, ContainerInterface $container)
    {
        $this->removeOgBlockData($container);
        $this->removeBreadcrumbBlockData($container);
        $this->deleteBlock($container);

        $eccubeConfig = $container->get('Eccube\Common\EccubeConfig');
        $path = $eccubeConfig->get('kernel.project_dir') . '/sitemap.xml';
        if(is_file($path))  
        {
            // delete file
            @chmod($path, 0777);
            @unlink($path);
        }
	}

    public function update(array $meta, ContainerInterface $container)
    {		
        $this->updateOgBlockData($container);
        $this->updateBreadcrumbBlockData($container);
        $this->copyBlock($container);
    }

    public function uninstall(array $meta, ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        // $this->migration($em->getConnection(), $meta['code'], '0');
        
        $this->removeOgBlockData($container);
        $this->removeBreadcrumbBlockData($container);
        $this->deleteBlock($container);

        $eccubeConfig = $container->get('Eccube\Common\EccubeConfig');
        $path = $eccubeConfig->get('kernel.project_dir') . '/sitemap.xml';
        if(is_file($path))  
        {
            // delete file
            @chmod($path, 0777);
            @unlink($path);
        }
    }

    private function copyBlock(ContainerInterface $container)
    {
        $date = date('YmdHis');
        $template_block_dir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        // backup file
        if($file->exists($template_block_dir.'/Block/'.$this->og_tags_filename.'.twig')) {
            $file->copy($template_block_dir.'/Block/'.$this->og_tags_filename.'.twig', $template_block_dir.'/Block/'.$this->og_tags_filename. $date .'.twig');
        }
        if($file->exists($template_block_dir.'/Block/'.$this->breadcrumb_filename.'.twig')) {
            $file->copy($template_block_dir.'/Block/'.$this->breadcrumb_filename.'.twig', $template_block_dir.'/Block/'.$this->breadcrumb_filename. $date .'.twig');
        }

        $file->copy(__DIR__.'/Resource/template/Block/'.$this->og_tags_filename.'.twig', $template_block_dir.'/Block/'.$this->og_tags_filename.'.twig');
        $file->copy(__DIR__.'/Resource/template/Block/'.$this->breadcrumb_filename.'.twig', $template_block_dir.'/Block/'.$this->breadcrumb_filename.'.twig');

        // copy default_frame
        // $file->copy(__DIR__.'/Resource/template/'.$this->default_frame_filename.'.twig', $template_block_dir.'/'.$this->default_frame_filename.'.twig');
    }

    private function deleteBlock(ContainerInterface $container)
    {
        $template_block_dir = $container->getParameter('eccube_theme_front_dir');
        $og_tag_block_dir = $template_block_dir.'/Block/'.$this->og_tags_filename.'.twig';
        $breadcrumb_block_dir = $template_block_dir.'/Block/'.$this->breadcrumb_filename.'.twig';

        $file = new Filesystem();
        if ($file->exists($og_tag_block_dir)){
			$file->remove($og_tag_block_dir);
        }
        
        if ($file->exists($breadcrumb_block_dir)){
			$file->remove($breadcrumb_block_dir);
		}
    }
    
    private function registerOgBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);
        $Block = $BlockRepository->findOneBy(['file_name' => $this->og_tags_filename]);
        
        if (!$Block) {
            $DeviceTypeRepository = $em->getRepository(DeviceType::class);
            $DeviceType = $DeviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);
            /** @var Block $Block */
            $Block = $BlockRepository->newBlock($DeviceType);

            // Blockの登録
            $Block->setName($this->og_tags_blockname)
                ->setFileName($this->og_tags_filename)
                ->setUseController(false)
                ->setDeletable(false);
            $em->persist($Block);
            $em->flush($Block);
        }

        $this->registerBlockForUnderlayerPages($Block, $container, Layout::TARGET_ID_HEAD);
        $this->registerBlockForTopPage($Block, $container, Layout::TARGET_ID_HEAD);
    }

    private function removeOgBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);
        $Block = $BlockRepository->findOneBy(['file_name' => $this->og_tags_filename]);

        if (!$Block) {
            return;
        }

        $em = $container->get('doctrine.orm.entity_manager');

        $blockPositions = $Block->getBlockPositions();
        foreach ($blockPositions as $BlockPosition) {
            $Block->removeBlockPosition($BlockPosition);
            $em->remove($BlockPosition);
        }

        $em->remove($Block);
        $em->flush();
    }

    private function updateOgBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);

        $Block = $BlockRepository->findOneBy(['file_name' => $this->og_tags_filename]);

        if (!$Block)
        {
            $this->registerOgBlockData($container);
            return;
        }

        $BlockPositionRepository = $em->getRepository(BlockPosition::class);
        $BlockPositionUnderlayerPage = $BlockPositionRepository->findOneBy(
            ['section' => Layout::TARGET_ID_HEAD,
            'Block' => $Block, 
            'layout_id' => Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE]
        );
        if (!$BlockPositionUnderlayerPage)
        {
            $this->registerBlockForUnderlayerPages($Block, $container, Layout::TARGET_ID_HEAD);
        }

        $BlockPositionTopPage = $BlockPositionRepository->findOneBy(
            ['section' => Layout::TARGET_ID_HEAD, 
            'Block' => $Block, 
            'layout_id'  => Layout::DEFAULT_LAYOUT_TOP_PAGE]
        );
        if (!$BlockPositionTopPage)
        {
            $this->registerBlockForTopPage($Block, $container, Layout::TARGET_ID_HEAD);
        }
    }

    private function registerBlockForUnderlayerPages(Block $Block, ContainerInterface $container, $target_id)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockPositionRepository = $em->getRepository(BlockPosition::class);
        $LayoutRepository = $em->getRepository(Layout::class);
        $blockPos = $BlockPositionRepository->findOneBy(
            ['section' => $target_id, 
            'Block' => $Block, 
            'layout_id'  => Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE]
        );
        if (!$blockPos) {
            // Register block position for Underlayer pages
            $blockPos = $BlockPositionRepository->findOneBy(
                ['section' => $target_id, 'layout_id' => Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE],
                ['block_row' => 'DESC']
            );

            $BlockPosition = new BlockPosition();

            // ブロックの順序を変更
            $BlockPosition->setBlockRow(1);
            if ($blockPos) {
                $blockRow = $blockPos->getBlockRow() + 1;
                $BlockPosition->setBlockRow($blockRow);
            }

            $LayoutDefault = $LayoutRepository->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);

            $BlockPosition->setLayout($LayoutDefault)
                ->setLayoutId($LayoutDefault->getId())
                ->setSection($target_id)
                ->setBlock($Block)
                ->setBlockId($Block->getId());

            $em->persist($BlockPosition);
            $em->flush($BlockPosition);
        }
    }

    private function registerBlockForTopPage(Block $Block, ContainerInterface $container, $target_id)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockPositionRepository = $em->getRepository(BlockPosition::class);
        $LayoutRepository = $em->getRepository(Layout::class);
        $blockPosTOP = $BlockPositionRepository->findOneBy(
            ['section' => $target_id, 
            'Block' => $Block, 
            'layout_id'  => Layout::DEFAULT_LAYOUT_TOP_PAGE]
        );
        if (!$blockPosTOP)
        {
            $blockPosTOP = $BlockPositionRepository->findOneBy(
                ['section'  => $target_id, 'layout_id'  => Layout::DEFAULT_LAYOUT_TOP_PAGE],
                ['block_row'    => 'DESC']
            );

            // Register block position for TOP page
            $BlockPositionTOP = new BlockPosition();

            $BlockPositionTOP->setBlockRow(1);
            if ($blockPosTOP)
            {
                $blockRow = $blockPosTOP->getBlockRow() + 1;
                $BlockPositionTOP->setBlockRow($blockRow);
            }
    
            $LayoutDefaultTOP = $LayoutRepository->find(Layout::DEFAULT_LAYOUT_TOP_PAGE);
    
            $BlockPositionTOP->setLayout($LayoutDefaultTOP)
                ->setLayoutId($LayoutDefaultTOP->getId())
                ->setSection($target_id)
                ->setBlock($Block)
                ->setBlockId($Block->getId());
    
            $em->persist($BlockPositionTOP);
            $em->flush($BlockPositionTOP);
        }
    }

    private function initConfigData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT NULL DEFAULT 3");				
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT NULL DEFAULT 0");			
			}
			
			// global_id_type for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id_type INT NULL DEFAULT 3");				
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN valid_price_month INT NOT NULL DEFAULT 0");			
			}		
			
			// valid_price_flg for product
			$stmt = $conn->executeQuery("SHOW COLUMNS FROM `plg_seoallone_product` LIKE 'updated_flg'");
			$cnt = $stmt->fetchAll();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN updated_flg TINYINT NOT NULL DEFAULT 0");				
			}		
		} elseif ($driver == 'pdo_pgsql') {
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT NOT NULL DEFAULT 3");				
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT NOT NULL DEFAULT 0");			
			}		
			
			// global_id_type for product
			$stmt = $conn->executeQuery("SELECT column_name FROM information_schema.columns WHERE table_name='plg_seoallone_product' and column_name='global_id_type'");
			$cnt = $stmt->fetchColumn();
			if (!$cnt) {
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN global_id_type INT NOT NULL DEFAULT 3");				
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
				$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_product ADD COLUMN valid_price_month INT NOT NULL DEFAULT 0");			
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
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN global_id_type INT NOT NULL DEFAULT 3");
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
					$stmt = $conn->executeQuery("ALTER TABLE plg_seoallone_config ADD COLUMN valid_price_month INT NOT NULL DEFAULT 0");
				}				
			}			
			
			// global_id
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
		}
		
		
        $ConfigRepository = $em->getRepository(Config::class);
        $Config = $ConfigRepository->findOneBy([]);
        if (!$Config)
        {
            $Config = new Config();
            $Config->setRichSnippetFlg(1);
            $Config->setSnsFlg(1);
            $Config->setSitemapFlg(1);
            $Config->setPaginationFlg(1);
            $Config->setBreadCrumbFlg(1);

            $Config->setFacebookFlg(1);
            $Config->setTwitterFlg(1);
            $Config->setLineFlg(1);
            $Config->setShopNameTopFlg(1);
            $Config->setGlobalIdFlg(0);
            $Config->setGlobalIdType(3);
            $Config->setValidPriceFlg(0);
            $Config->setValidPriceMonth(0);

            $em->persist($Config);
            $em->flush($Config);
        }
    }

    private function registerBreadcrumbBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);
        $DeviceTypeRepository = $em->getRepository(DeviceType::class);

        $Block = $BlockRepository->findOneBy(['file_name' => $this->breadcrumb_filename]);
        
        if (!$Block) {
            $DeviceType = $DeviceTypeRepository->find(DeviceType::DEVICE_TYPE_PC);
            /** @var Block $Block */
            $Block = $BlockRepository->newBlock($DeviceType);

            // Blockの登録
            $Block->setName($this->breadcrumb_blockname)
                ->setFileName($this->breadcrumb_filename)
                ->setUseController(false)
                ->setDeletable(false);
            $em->persist($Block);
            $em->flush($Block);
        }

        $this->registerBlockForUnderlayerPages($Block, $container, Layout::TARGET_ID_HEADER);
        $this->registerBlockForTopPage($Block, $container, Layout::TARGET_ID_HEADER);
    }

    private function removeBreadcrumbBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);

        $Block = $BlockRepository->findOneBy(['file_name' => $this->breadcrumb_filename]);

        if (!$Block) {
            return;
        }

        $blockPositions = $Block->getBlockPositions();
        foreach ($blockPositions as $BlockPosition) {
            $Block->removeBlockPosition($BlockPosition);
            $em->remove($BlockPosition);
        }

        $em->remove($Block);
        $em->flush();
    }

    private function updateBreadcrumbBlockData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $BlockRepository = $em->getRepository(Block::class);
        $BlockPositionRepository = $em->getRepository(BlockPosition::class);

        $Block = $BlockRepository->findOneBy(['file_name' => $this->breadcrumb_filename]);

        if (!$Block)
        {
            $this->registerBreadcrumbBlockData($container);
            return;
        }

        $BlockPositionUnderlayerPage = $BlockPositionRepository->findOneBy(
            ['section' => Layout::TARGET_ID_HEADER,
            'Block' => $Block, 
            'layout_id' => Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE]
        );
        if (!$BlockPositionUnderlayerPage)
        {
            $this->registerBlockForUnderlayerPages($Block, $container, Layout::TARGET_ID_HEADER);
        }

        $BlockPositionTopPage = $BlockPositionRepository->findOneBy(
            ['section' => Layout::TARGET_ID_HEADER, 
            'Block' => $Block, 
            'layout_id'  => Layout::DEFAULT_LAYOUT_TOP_PAGE]
        );
        if (!$BlockPositionTopPage)
        {
            $this->registerBlockForTopPage($Block, $container, Layout::TARGET_ID_HEADER);
        }
    }

    private function initDefaultMetaCategoryData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $PageRepository = $em->getRepository(Page::class);
        $SEOAllOneDefaultRepository = $em->getRepository(SEOAllOneDefault::class);

        $CategoryPage = $PageRepository->getPageByRoute('product_list');
        if(!$CategoryPage) {
            return;
        }

        $SEOAllOneCategory = $SEOAllOneDefaultRepository->findOneBy(['Page' => $CategoryPage]);
        if (!$SEOAllOneCategory)
        {
            $SEOAllOneCategory = new SEOAllOneDefault();

            $now = date('Y-m-d H:i:s', time());
            $SEOAllOneCategory->setPage($CategoryPage);
            $SEOAllOneCategory->setTitle('##category.name##の商品一覧');
            $SEOAllOneCategory->setDescription('##category.name##の商品一覧ページです。');
            $SEOAllOneCategory->setKeyword('##category.name##');
            $SEOAllOneCategory->setOGType('article');
            $SEOAllOneCategory->setDelFlg(0);
            $SEOAllOneCategory->setCreateDate($now);
            $SEOAllOneCategory->setUpdateDate($now);

            $em->persist($SEOAllOneCategory);
            $em->flush($SEOAllOneCategory);
        }
    }

    private function initDefaultMetaProductData(ContainerInterface $container)
    {
        $em = $container->get('doctrine.orm.entity_manager');
        $PageRepository = $em->getRepository(Page::class);
        $SEOAllOneDefaultRepository = $em->getRepository(SEOAllOneDefault::class);

        $ProductPage = $PageRepository->getPageByRoute('product_detail');
        if(!$ProductPage) {
            return;
        }
        $SEOAllOneProduct = $SEOAllOneDefaultRepository->findOneBy(['Page' => $ProductPage]);
        if (!$SEOAllOneProduct)
        {
            $SEOAllOneProduct = new SEOAllOneDefault();

            $now = date('Y-m-d H:i:s', time());
            $SEOAllOneProduct->setPage($ProductPage);
            $SEOAllOneProduct->setTitle('##product.name##の通販情報');
            $SEOAllOneProduct->setDescription('##product.name####（category.name）##を##product.sell_price##円で販売しています。##product.name##の詳細情報をご覧ください。');
            $SEOAllOneProduct->setKeyword('##product.name####,category.name##');
            $SEOAllOneProduct->setOGType('product');
            $SEOAllOneProduct->setDelFlg(0);
            $SEOAllOneProduct->setCreateDate($now);
            $SEOAllOneProduct->setUpdateDate($now);

            $em->persist($SEOAllOneProduct);
            $em->flush($SEOAllOneProduct);
        }
    }
}

?>