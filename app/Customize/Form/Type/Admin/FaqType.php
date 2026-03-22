<?php

namespace Customize\Form\Type\Admin;

use Customize\Entity\Faq;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FaqType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('question', TextType::class, [
                'label' => '質問',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 1000]),
                ],
            ])
            ->add('answer', TextareaType::class, [
                'label' => '回答',
                'required' => true,
                'attr' => ['rows' => 5],
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('sort_no', IntegerType::class, [
                'label' => '並び順',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Range(['min' => 0]),
                ],
            ])
            ->add('visible', CheckboxType::class, [
                'label' => '表示する',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Faq::class,
        ]);
    }
}
