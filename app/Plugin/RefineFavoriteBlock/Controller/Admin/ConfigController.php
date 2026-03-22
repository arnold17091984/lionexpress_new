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

namespace Plugin\RefineFavoriteBlock\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\RefineFavoriteBlock\Form\Type\Admin\RefineFavoriteBlockConfigType;
use Plugin\RefineFavoriteBlock\Repository\RefineFavoriteBlockConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends AbstractController
{
    /**
     * @var RefineFavoriteBlockConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param RefineFavoriteBlockConfigRepository $configRepository
     */
    public function __construct(RefineFavoriteBlockConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/refine_favorite_block/config", name="refine_favorite_block_admin_config")
     * @Template("@RefineFavoriteBlock/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(RefineFavoriteBlockConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush($Config);

            // 登録しました。
            $this->addSuccess('refine_favorite_block.admin.config.save.success', 'admin');

            return $this->redirectToRoute('refine_favorite_block_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
