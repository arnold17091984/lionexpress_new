<?php
/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2018 lecast system.
  * @author Tetsuji Shiro 
  *
  * このプラグインは再販売禁止です。
  */

namespace Plugin\LecastTableList41\Controller;

use Eccube\Controller\AbstractController;
use Plugin\LecastTableList41\Entity\TableTemplate;
use Plugin\LecastTableList41\Form\Type\TableTemplateType;
use Plugin\LecastTableList41\Repository\TableTemplateRepository;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Class TableListController.
 */
class TableListController extends AbstractController
{
    /**
     * @var TableTemplateRepository
     */
    protected $tableTemplateRepository;

    /**
     * LecastTableList41Controller constructor.
     *
     * @param TableTemplateRepository $tableTemplateRepository
     */
    public function __construct(
        TableTemplateRepository $tableTemplateRepository
    ) {
        $this->tableTemplateRepository = $tableTemplateRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/plg_admin_table_list", name="admin_table_list")
     * @Template("@LecastTableList41/admin/admin_table_template.twig")
     * 
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $tableTemplates = $this->tableTemplateRepository->findAll();
        $builder = $this->formFactory
            ->createBuilder(TableTemplateType::class, $tableTemplates);

        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $template = new TableTemplate();
            if(empty($request->request->get("admin_product")["template_value"])){
                $this->addError('テーブルが入力されていません。', 'admin');
                return $this->redirectToRoute('admin_table_list');
            }
            $template->setTemplateName($request->request->get("table_template")["template_name"]);
            $template->setTemplateValue($request->request->get("admin_product")["template_value"]);
            $this->tableTemplateRepository->save($template);
            return $this->redirectToRoute('admin_table_list');
        }
        $temp = [];
        foreach ($tableTemplates as $val){
            $temp[] = [
                "id" => $val['id'],
                "template_name" => $val['template_name'],
                "template_value" => TableTemplate::buildHtml($val['template_value'],true),
            ];
        }
        return [
            'form' => $form->createView(),
            'pagination' => $temp,
            'total_item_count' => count($tableTemplates),
        ];
    }

    /**
     * @Route("/%eccube_admin_route%/plg_admin_table_list/{id}/delete", requirements={"id" = "\d+"}, name="admin_table_list_delete")
     * 
     * @param Request $request
     * @param int $page_no
     * @param Paginator $paginator
     *
     * @return array
     */
    public function delete(Request $request, $id = null)
    {
        // Id valid
        if (!$id) {
            return $this->redirectToRoute('admin_table_list');
        }

        $em = $this->tableTemplateRepository->findOneBy(["id" => $id]);
        if (!$em) {
            throw new NotFoundHttpException('データがありません');
        }
        $this->tableTemplateRepository->remove($em);
        return $this->redirectToRoute('admin_table_list');
    }

}
