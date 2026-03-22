<?php

namespace Customize\Controller\Admin;

use Customize\Entity\Faq;
use Customize\Form\Type\Admin\FaqType;
use Customize\Repository\FaqRepository;
use Eccube\Controller\AbstractController;
use Eccube\Util\CacheUtil;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FaqController extends AbstractController
{
    /** @var FaqRepository */
    private $faqRepository;

    public function __construct(FaqRepository $faqRepository)
    {
        $this->faqRepository = $faqRepository;
    }

    /**
     * @Route("/%eccube_admin_route%/content/faq", name="admin_content_faq", methods={"GET"})
     * @Template("@admin/Customize/Faq/index.twig")
     */
    public function index(): array
    {
        $faqs = $this->faqRepository->findAllOrdered();

        return ['faqs' => $faqs];
    }

    /**
     * @Route("/%eccube_admin_route%/content/faq/new", name="admin_content_faq_new", methods={"GET", "POST"})
     * @Route("/%eccube_admin_route%/content/faq/{id}/edit", requirements={"id" = "\d+"}, name="admin_content_faq_edit", methods={"GET", "POST"})
     * @Template("@admin/Customize/Faq/edit.twig")
     */
    public function edit(Request $request, CacheUtil $cacheUtil, ?int $id = null)
    {
        if ($id) {
            $Faq = $this->faqRepository->find($id);
            if (!$Faq) {
                throw new NotFoundHttpException();
            }
        } else {
            $Faq = new Faq();
            $count = count($this->faqRepository->findAll());
            $Faq->setSortNo($count + 1);
        }

        $form = $this->formFactory->createBuilder(FaqType::class, $Faq)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->faqRepository->save($Faq);
            $cacheUtil->clearDoctrineCache();
            $this->addSuccess('保存しました', 'admin');

            return $this->redirectToRoute('admin_content_faq_edit', ['id' => $Faq->getId()]);
        }

        return ['form' => $form->createView(), 'Faq' => $Faq];
    }

    /**
     * @Route("/%eccube_admin_route%/content/faq/{id}/delete", requirements={"id" = "\d+"}, name="admin_content_faq_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Faq $Faq, CacheUtil $cacheUtil)
    {
        $this->isTokenValid();

        $this->faqRepository->delete($Faq);
        $cacheUtil->clearDoctrineCache();
        $this->addSuccess('削除しました', 'admin');

        return $this->redirectToRoute('admin_content_faq');
    }
}
