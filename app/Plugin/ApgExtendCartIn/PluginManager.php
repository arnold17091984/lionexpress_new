<?php

/*
 * This file is part of the Flash Sale plugin
 *
 * Copyright(c) ECCUBE VN LAB. All Rights Reserved.
 *
 * https://www.facebook.com/groups/eccube.vn
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ApgExtendCartIn;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\Constant;
use Eccube\Entity\Block;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{

    const BLOCK_NAME = [

        '(商品詳細)カートに入れる - リスト表示' => 'plg_cart_in_list',
        '(商品詳細)カートに入れる - グリッド表示' => 'plg_cart_in_grid',
    ];

    private $blockBasePath;


    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        $this->blockBasePath = __DIR__ . '/Resource/template/block/';
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        foreach (self::BLOCK_NAME as $blockName => $fileName) {
            $this->removeDataBlock($container, $fileName);
        }
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
//        $this->migration($entityManager->getConnection(), $meta['code'], '0');
    }

    /**
     * @param null|array $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function enable(array $meta = null, ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        foreach (self::BLOCK_NAME as $blockName => $fileName) {
            $this->copyBlock($container, $fileName);
            $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => $fileName]);
            if (is_null($Block)) {
                $this->createDataBlock($container, $blockName, $fileName);
            }
        }
//        $this->migration($entityManager->getConnection(), $meta['code']);
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    public function disable(array $meta = null, ContainerInterface $container)
    {
        foreach (self::BLOCK_NAME as $blockName => $fileName) {
            $this->removeDataBlock($container, $fileName);
            $this->removeBlock($container, $fileName);
        }
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta = null, ContainerInterface $container)
    {
        foreach (self::BLOCK_NAME as $blockName => $fileName) {
            $this->copyBlock($container, $fileName);
        }
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
//        $this->migration($entityManager->getConnection(), $meta['code']);
    }

    /**
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    private function createDataBlock(ContainerInterface $container, $blockName, $fileName)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        $DeviceType = $entityManager->getRepository(DeviceType::class)->find(DeviceType::DEVICE_TYPE_PC);
        try {
            /** @var Block $Block */
            $Block = $entityManager->getRepository(Block::class)->newBlock($DeviceType);
            $Block->setName($blockName)
                ->setFileName($fileName)
                ->setUseController(Constant::DISABLED);
            $entityManager->persist($Block);
            $entityManager->flush($Block);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @throws \Exception
     */
    private function removeDataBlock(ContainerInterface $container, $fileName)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine')->getManager();
        /** @var \Eccube\Entity\Block $Block */
        $Block = $entityManager->getRepository(Block::class)->findOneBy(['file_name' => $fileName]);
        if (!$Block) {
            return;
        }
        try {
            $blockPositions = $Block->getBlockPositions();
            /** @var \Eccube\Entity\BlockPosition $BlockPosition */
            foreach ($blockPositions as $BlockPosition) {
                $Block->removeBlockPosition($BlockPosition);
                $entityManager->remove($BlockPosition);
            }
            $entityManager->remove($Block);
            $entityManager->flush();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param ContainerInterface $container
     * @param $fileName
     */
    private function copyBlock(ContainerInterface $container, $fileName)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        $filePath = $this->blockBasePath . $fileName . '.twig';
        $file->copy($filePath, $templateDir . '/Block/' . $fileName . '.twig');
    }

    /**
     * Remove block template.
     *
     * @param ContainerInterface $container
     */
    private function removeBlock(ContainerInterface $container, $fileName)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        $file->remove($templateDir . '/Block/' . $fileName . '.twig');
    }

}
