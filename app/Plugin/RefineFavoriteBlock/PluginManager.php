<?php

/*
 * This file is part of Refine
 *
 * Copyright(c) 2024 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineFavoriteBlock;

use Eccube\Entity\Block;
use Eccube\Entity\BlockPosition;
use Eccube\Entity\Layout;
use Eccube\Entity\Master\DeviceType;
use Eccube\Plugin\AbstractPluginManager;
use Plugin\RefineFavoriteBlock\Entity\RefineFavoriteBlockConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginManager extends AbstractPluginManager
{
    /**
     * @var string コピー元ブロックファイル
     */
    private $originBlock;

    /**
     * @var string ブロック名
     */
    private $blockName = '新着お気に入り商品';

    /**
     * @var string ブロックファイル名
     */
    private $blockFileName = 'refine_favorite';

    /**
     * PluginManager constructor.
     */
    public function __construct()
    {
        // コピー元ブロックファイル
        $this->originBlock = __DIR__.'/Resource/template/default/Block/'.$this->blockFileName.'.twig';
    }

    /**
     * @param array $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        // ブロックの削除
        $this->removeDataBlock($container);
        $this->removeBlock($container);
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function enable(array $meta = null, ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();

        $this->copyBlock($container);
        $Block = $em->getRepository(Block::class)->findOneBy(['file_name' => $this->blockFileName]);

        if (is_null($Block))
        {
            $this->createDataBlock($container);
        }

        // プラグイン設定の初期値を登録
        $RefineFavoriteBlockConfig = new RefineFavoriteBlockConfig();
        $RefineFavoriteBlockConfig->setDisplayNum(5);
        $RefineFavoriteBlockConfig->setFontSize('medium');
        $em->persist($RefineFavoriteBlockConfig);
        $em->flush($RefineFavoriteBlockConfig);
    }
    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     * @throws \Exception
     */
    public function disable(array $meta = null, ContainerInterface $container)
    {
        $this->removeDataBlock($container);
    }

    /**
     * @param array|null $meta
     * @param ContainerInterface $container
     */
    public function update(array $meta = null, ContainerInterface $container)
    {
        $this->copyBlock($container);
    }

    /**
     * ブロックを登録.
     *
     * @param ContainerInterface $container
     * @throws \Exception
     */
    private function createDataBlock(ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();
        $DeviceType = $em->getRepository(DeviceType::class)->find(DeviceType::DEVICE_TYPE_PC);

        try
        {
            /** @var Block $Block */
            $Block = $em->getRepository(Block::class)->newBlock($DeviceType);

            // ブロックの登録
            $Block->setName($this->blockName)
                ->setFileName($this->blockFileName)
                ->setUseController(true)
                ->setDeletable(false);
            $em->persist($Block);
            $em->flush($Block);

            // ブロック位置がすでに登録されている場合は登録しない
            $blockPos = $em->getRepository(BlockPosition::class)->findOneBy(['Block' => $Block]);
            if ($blockPos)
            {
                return;
            }

            // ブロック位置の登録
            $blockPos = $em->getRepository(BlockPosition::class)->findOneBy(
                ['section' => Layout::TARGET_ID_MAIN_BOTTOM, 'layout_id' => Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE],
                ['block_row' => 'DESC']
            );

            $BlockPosition = new BlockPosition();

            // ブロックの順序を変更
            $BlockPosition->setBlockRow(1);
            if ($blockPos)
            {
                $blockRow = $blockPos->getBlockRow() + 1;
                $BlockPosition->setBlockRow($blockRow);
            }

            $LayoutDefault = $em->getRepository(Layout::class)->find(Layout::DEFAULT_LAYOUT_UNDERLAYER_PAGE);

            $BlockPosition->setLayout($LayoutDefault)
                ->setLayoutId($LayoutDefault->getId())
                ->setSection(Layout::TARGET_ID_MAIN_BOTTOM)
                ->setBlock($Block)
                ->setBlockId($Block->getId());

            $em->persist($BlockPosition);
            $em->flush($BlockPosition);
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    /**
     * ブロックを削除.
     *
     * @param ContainerInterface $container
     * @throws \Exception
     */
    private function removeDataBlock(ContainerInterface $container)
    {
        $em = $container->get('doctrine')->getManager();

        // ブロックの取得 ( file_nameはアプリケーションの仕組み上必ずユニーク )
        /** @var \Eccube\Entity\Block $Block */
        $Block = $em->getRepository(Block::class)->findOneBy(['file_name' => $this->blockFileName]);

        if (!$Block)
        {
            return;
        }

        try
        {
            // ブロック位置の削除
            $blockPositions = $Block->getBlockPositions();
            /** @var \Eccube\Entity\BlockPosition $BlockPosition */
            foreach ($blockPositions as $BlockPosition)
            {
                $Block->removeBlockPosition($BlockPosition);
                $em->remove($BlockPosition);
            }

            // ブロックの削除
            $em->remove($Block);
            $em->flush();
        }
        catch (\Exception $e)
        {
            throw $e;
        }
    }

    /**
     * ブロックテンプレートのコピー
     *
     * @param ContainerInterface $container
     */
    private function copyBlock(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        // ファイルコピー
        $file = new Filesystem();

        if (!$file->exists($templateDir.'/Block/'.$this->blockFileName.'.twig'))
        {
            // ブロックファイルをコピー
            $file->copy($this->originBlock, $templateDir.'/Block/'.$this->blockFileName.'.twig');
        }
    }

    /**
     * ブロックテンプレートの削除
     *
     * @param ContainerInterface $container
     */
    private function removeBlock(ContainerInterface $container)
    {
        $templateDir = $container->getParameter('eccube_theme_front_dir');
        $file = new Filesystem();
        $file->remove($templateDir.'/Block/'.$this->blockFileName.'.twig');
    }
}
