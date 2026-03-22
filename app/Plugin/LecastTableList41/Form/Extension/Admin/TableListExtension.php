<?php

/*
  * This file is part of the LecastTableList41 plugin
  *
  * Copyright (C) >=2018 lecast system.
  * @author Tetsuji Shiro
  *
  * このプラグインは再販売禁止です。
  */

namespace Plugin\LecastTableList41\Form\Extension\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Product;
use Eccube\Form\Type\Admin\ProductType;

use Plugin\LecastTableList41\Entity\TableTemplate;
use Plugin\LecastTableList41\Repository\TableTemplateRepository;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Class TableListExtension.
 */
class TableListExtension extends AbstractTypeExtension
{
    /**
     * @var EccubeConfig
     */
    private $eccubeConfig;

    /**
     * @var TableTemplateRepository
     */
    private $tableTemplateRepository;

    /**
     * ProductTypeExtension constructor.
     *
     * @param EccubeConfig $eccubeConfig
     * @param TableTemplateRepository $tableTemplateRepository
     */
    public function __construct(EccubeConfig $eccubeConfig, TableTemplateRepository $tableTemplateRepository)
    {
        $this->eccubeConfig = $eccubeConfig;
        $this->tableTemplateRepository = $tableTemplateRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = $this->tableTemplateRepository->findAll();
        $type['テンプレートを選択'] = '';
        foreach($types as $key){
            $type[$key['template_name']] = TableTemplate::buildHtml($key['template_value']);
        }
        $Product = $options['data'];
        $Product->template_is_valid = false;
        $builder
            ->add('TableTemplate', ChoiceType::class, [
                'mapped' => false,
                'choices' => $type,
                'required' => false,
                'attr' => array('class' => ''),
            ])
            ->add('template_value', HiddenType::class, [
                'required' => false,
                'eccube_form_options' => [
                    'auto_render' => true,
                ],
            ]);
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var Product $Product */
            $Product = $event->getData();
            $form = $event->getForm();
            $form['template_value']->setData($Product->getTemplateValue());
        });
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $Product = $event->getData();
            $form = $event->getForm();
	        if(!$form->isValid()) {
				$Product->setTemplateValue($form['template_value']->getData());
				$Product->template_is_valid = true;
	        }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ProductType::class;
    }
    public static function getExtendedTypes(): iterable
    {
        return [ProductType::class];
    }
}
