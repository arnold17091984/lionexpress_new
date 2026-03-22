<?php

/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2018 lecast system.
  * @author Tetsuji Shiro 
  *
  * このプラグインは再販売禁止です。
  */

namespace Plugin\LecastTableList41\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Annotation as Eccube;
use Plugin\LecastTableList41\Entity\TableTemplate;

/**
 * @Eccube\EntityExtension("Eccube\Entity\Product")
 */
trait ProductTrait
{
    /**
     * @var string
     *
     * @ORM\Column(name="template_value", type="text", nullable=true)
     */
    private $template_value;


    /**
     * @return text $TemplateValue
     */
    public function getTemplateValue()
    {
        return TableTemplate::buildHtml($this->template_value, true);
    }

    /**
     * @param text $TemplateValue
     */
    public function setTemplateValue($TemplateValue)
    {
        $this->template_value = $TemplateValue;
    }

}
