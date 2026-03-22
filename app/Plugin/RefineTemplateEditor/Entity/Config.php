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
namespace Plugin\RefineTemplateEditor\Entity;

use Doctrine\ORM\Mapping as ORM;

if (!class_exists('\Plugin\RefineTemplateEditor\Entity\Config', false)) {
    /**
     * Config
     *
     * @ORM\Table(name="plg_refine_template_editor_config")
     * @ORM\Entity(repositoryClass="Plugin\RefineTemplateEditor\Repository\ConfigRepository")
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
         * @ORM\Column(name="name", type="string", length=255)
         */
        private $name;

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
        public function getName()
        {
            return $this->name;
        }

        /**
         * @param string $name
         *
         * @return $this;
         */
        public function setName($name)
        {
            $this->name = $name;

            return $this;
        }
    }
}
