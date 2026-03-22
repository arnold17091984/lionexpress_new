<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Customize\Controller\Block;

use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Form\Type\SearchProductBlockType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;

class CustomSearchProductController extends AbstractController
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack
    ) {
        $this->requestStack = $requestStack;
    }

    /**
     * @Route("/block/sp_search", name="block_sp_search", methods={"GET"})
     * @Template("Block/sp_search.twig")
     */
    public function index(Request $request)
    {
        $builder = $this->formFactory
            ->createNamedBuilder('', SearchProductBlockType::class)
            ->setMethod('GET');

        $event = new EventArgs(
            [
                'builder' => $builder,
            ],
            $request
        );

        $this->eventDispatcher->dispatch(EccubeEvents::FRONT_BLOCK_SEARCH_PRODUCT_INDEX_INITIALIZE, $event);

        // getMasterRequest() is deprecated in Symfony 5.3+; use getMainRequest() after upgrade
        $request = $this->requestStack->getMasterRequest();

        $form = $builder->getForm();
        $form->handleRequest($request);

        return [
            'form' => $form->createView(),
        ];
    }
}
