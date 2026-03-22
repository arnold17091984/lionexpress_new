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

namespace Eccube\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class TopController extends AbstractController
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     * @Template("index.twig")
     */
    public function index()
    {
        if (isset($_GET['ref'])) {
            $refferalCode = $_GET['ref'];
    
            setcookie('ref', $refferalCode.'_'.time(), time() + (86400 * 30), "/"); // 86400 = 1 day * 30 = 30 Days
        }
        return [];
    }
}