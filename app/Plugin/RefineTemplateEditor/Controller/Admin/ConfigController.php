<?php
/*
 * This file is part of Refine
 *
 * Copyright(c) 2022 Refine Co.,Ltd. All Rights Reserved.
 *
 * https://www.re-fine.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RefineTemplateEditor\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\RefineTemplateEditor\Form\Type\Admin\ConfigType;
use Plugin\RefineTemplateEditor\Repository\ConfigRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConfigController extends AbstractController
{
    /**
     * @var ConfigRepository
     */
    protected $configRepository;

    /**
     * ConfigController constructor.
     *
     * @param ConfigRepository $configRepository
     */
    public function __construct(ConfigRepository $configRepository)
    {
        $this->configRepository = $configRepository;
    }

    /**
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->get();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $Config = $form->getData();
            $this->entityManager->persist($Config);
            $this->entityManager->flush();
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('refine_template_editor_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
