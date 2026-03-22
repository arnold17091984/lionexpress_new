<?php

namespace Plugin\ApgExtendCartIn\Controller\Admin;

use Eccube\Controller\AbstractController;
use Plugin\ApgExtendCartIn\Entity\Config;
use Plugin\ApgExtendCartIn\Entity\Domain\CartType;
use Plugin\ApgExtendCartIn\Form\Type\Admin\ConfigType;
use Plugin\ApgExtendCartIn\Repository\ConfigRepository;
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
     * @Route("/%eccube_admin_route%/apg_extend_cart_in/config", name="apg_extend_cart_in_admin_config")
     * @Template("@ApgExtendCartIn/admin/config.twig")
     */
    public function index(Request $request)
    {
        $Config = $this->configRepository->getOrNew();
        $form = $this->createForm(ConfigType::class, $Config);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Config $Config */
            $Config = $form->getData();
            if (empty($Config->getCartType())) {
                $Config->setCartType(CartType::STANDARD);
            }
            $this->configRepository->save($Config);
            $this->entityManager->flush($Config);
            $this->addSuccess('登録しました。', 'admin');

            return $this->redirectToRoute('apg_extend_cart_in_admin_config');
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
