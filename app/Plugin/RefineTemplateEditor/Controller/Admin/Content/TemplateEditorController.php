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

namespace Plugin\RefineTemplateEditor\Controller\Admin\Content;

use Eccube\Controller\AbstractController;
use Eccube\Entity\Plugin;
use Eccube\Repository\PluginRepository;
use Eccube\Util\CacheUtil;
use Eccube\Util\StringUtil;
use Plugin\RefineTemplateEditor\Form\Type\Admin\TemplateEditType;
use Plugin\RefineTemplateEditor\Service\TemplateService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Twig\Environment;

class TemplateEditorController extends AbstractController
{
    /** @var PluginRepository */
    protected $pluginRepository;

    /** @var TemplateService */
    protected $templateService;

    public function __construct(PluginRepository $pluginRepository, TemplateService $templateService)
    {
        $this->pluginRepository = $pluginRepository;
        $this->templateService = $templateService;
    }

    /**
     * @Route("/%eccube_admin_route%/content/refine_template_editor/template_editor", name="refine_template_editor_content_template_editor_index")
     * @Template("@RefineTemplateEditor/admin/Content/template_list.twig")
     */
    public function index(Request $request)
    {
        $this->templateService->sessionInitialize();

        $templateDir = sprintf('%s/app/template/%s/',
            $this->getParameter('kernel.project_dir'),
            $this->getParameter('eccube.theme'));
        $themeTemplates = $this->templateService->getTemplates($templateDir);
        $themeTemplates = $this->templateService->setSessionTemplateCode('theme', $themeTemplates);

        $adminDir = sprintf('%s/app/template/admin/',
            $this->getParameter('kernel.project_dir'));
        $adminTemplates = $this->templateService->getTemplates($adminDir);
        $adminTemplates = $this->templateService->setSessionTemplateCode('admin', $adminTemplates);

        $templates = [
            'theme' => $themeTemplates,
            'admin' => $adminTemplates,
        ];

        // プラグインごとにテンプレートファイル
        /** @var $plugins Plugin[] */
        $plugins = $this->pluginRepository->findAllEnabled();
        foreach ($plugins as $plugin) {
            $pluginCode = $plugin->getCode();
            $pluginDir = sprintf('%s/app/Plugin/%s',
                $this->getParameter('kernel.project_dir'),
                $pluginCode);
            $pluginTemplates = $this->templateService->getTemplates($pluginDir);
            $pluginTemplates = $this->templateService->setSessionTemplateCode($pluginCode, $pluginTemplates);
            $templates[$plugin->getName()] = $pluginTemplates;
        }
        return [
            'Templates' => $templates,
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/content/refine_template_editor/template/{code}/edit", name="refine_template_editor_content_template_editor_edit", methods={"GET", "POST"})
     * @Template("@RefineTemplateEditor/admin/Content/template_edit.twig")
     */
    public function edit(Request $request, string $code, Environment $twig, CacheUtil $cacheUtil)
    {
        $template = $this->templateService->getTemplateInfoFromSession($code);
        if (!$template) {
            // templateが見つからない場合はNotFound
            log_error('template not found.', ['code' => $code]);
            throw new NotFoundHttpException();
        }
        $path = $template['path'];
        $fs = new Filesystem();
        if (!$fs->exists($path)) {
            log_error('template not found.', ['template' => $template]);
            throw new NotFoundHttpException();
        }
        $tplData = file_get_contents($path);


        $form = $this->formFactory->createBuilder(TemplateEditType::class)
            ->getForm()
            ->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $templateData = $form->get('tpl_data')->getData();
            $convertTemplateData = StringUtil::convertLineFeed($templateData);
            $fs->dumpFile($path, $convertTemplateData);
            $this->addSuccess('admin.common.save_complete', 'admin');
            $cacheUtil->clearTwigCache();
            return $this->redirectToRoute('refine_template_editor_content_template_editor_index');
        } else if ($request->getMethod() === 'GET' && !$form->isSubmitted()) {
            // templateのパス
            $form->get('name')->setData($template['name']);
            $form->get('path')->setData($template['path']);
            $form->get('tpl_data')->setData($tplData);
        }


        return [
            'code' => $template['code'],
            'form' => $form->createView(),
        ];


    }

}
