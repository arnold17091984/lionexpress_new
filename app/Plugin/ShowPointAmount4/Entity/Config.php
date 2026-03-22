<?php

/*
 * This file is part of ShowPointAmount4
 *
 * Copyright(c) U-Mebius Inc. All Rights Reserved.
 *
 * https://umebius.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ShowPointAmount4\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_show_point_amount4_admin_config")
 * @ORM\Entity(repositoryClass="Plugin\ShowPointAmount4\Repository\ConfigRepository")
 */
class Config
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="option_show_in_cart", type="boolean", options={"default" : true})
     */
    private $option_show_in_cart = true;

    /**
     * @var string
     *
     * @ORM\Column(name="option_show_in_product_detail", type="boolean", options={"default" : true})
     */
    private $option_show_in_product_detail = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function isOptionShowInCart()
    {
        return $this->option_show_in_cart;
    }

    /**
     * @param string $option_show_in_cart
     *
     * @return Config
     */
    public function setOptionShowInCart($option_show_in_cart)
    {
        $this->option_show_in_cart = $option_show_in_cart;

        return $this;
    }

    /**
     * @return string
     */
    public function isOptionShowInProductDetail()
    {
        return $this->option_show_in_product_detail;
    }

    /**
     * @param string $option_show_in_product_detail
     *
     * @return Config
     */
    public function setOptionShowInProductDetail($option_show_in_product_detail)
    {
        $this->option_show_in_product_detail = $option_show_in_product_detail;

        return $this;
    }
}
