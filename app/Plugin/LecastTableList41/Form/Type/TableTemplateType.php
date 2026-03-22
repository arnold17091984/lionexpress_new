<?php
/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2017 lecast system.
  * @author Tetsuji Shiro 
  *
  * このプラグインは再販売禁止です。
  */

namespace Plugin\LecastTableList41\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Class TableTemplateType.
 */
class TableTemplateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('template_name', TextType::class, array(
                'required' => true,
                'trim' => true,
                'attr' => array(
                    'placeholder' => "テンプレート名",
                ),
            ))
            ->add('template_value', HiddenType::class, array(
                'required' => true,
                'trim' => true,
            ));
    }

    public function getName()
    {
        return 'admin_itemtabletemplate';
    }
}
