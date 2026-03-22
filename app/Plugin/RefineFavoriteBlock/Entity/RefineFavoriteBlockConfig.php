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

namespace Plugin\RefineFavoriteBlock\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Config
 *
 * @ORM\Table(name="plg_refine_favorite_block_config")
 * @ORM\Entity(repositoryClass="Plugin\RefineFavoriteBlock\Repository\RefineFavoriteBlockConfigRepository")
 */
class RefineFavoriteBlockConfig
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
     * @var int
     *
     * @ORM\Column(name="display_num", type="integer", options={"unsigned":true, "notnull":false})
     */
    private $display_num;

    /**
     * @var string
     *
     * @ORM\Column(name="font_size", type="text", options={"notnull":false})
     */
    private $font_size;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDisplayNum()
    {
        return $this->display_num;
    }

    /**
     * @param int $display_num
     *
     * @return $this;
     */
    public function setDisplayNum($display_num)
    {
        $this->display_num = $display_num;

        return $this;
    }

    /**
     * @return string
     */
    public function getFontSize()
    {
      return $this->font_size;
    }

    /**
     * @param string $font_size
     *
     * @return $this;
     */
    public function setFontSize($font_size)
    {
      $this->font_size = $font_size;

      return $this;
    }
}
