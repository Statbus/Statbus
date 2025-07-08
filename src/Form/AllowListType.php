<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AllowListType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('ckey', TextType::class, [
                'row_attr' => ['class' => 'col-md-6']
            ])
            ->add('expiration', ChoiceType::class, [
                'row_attr' => ['class' => 'col-md-6'],
                'choices' => [
                    '1 Hour' => 1,
                    '6 Hours' => 6,
                    '12 Hours' => 12,
                    '24 Hours' => 24
                ],
                'help' => 'How long this allow list entry will be valid for'
            ])
            ->add('reason', TextareaType::class, [
                'row_attr' => ['class' => 'col-md-12'],
                'help' => 'Briefly explain why you are granting this person access'
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'form' => 'allowListForm',
                    'class' => 'btn btn-success',
                    'label' => 'Add to List'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'class' => 'form modal-body row',
                'autocomplete' => 'off',
                'name' => 'allowListForm',
                'id' => 'allowListForm'
            ]
        ]);
    }
}
