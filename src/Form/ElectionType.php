<?php

namespace App\Form;

use App\Enum\Election\AnonymityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElectionType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'class' => 'form-control-lg'
                ]
            ])
            ->add('start', DateType::class, [
                'help' => 'When should this election start? (you will not be able to modify this election or the candidate list after it starts)'
            ])
            ->add('end', DateType::class, [])
            ->add('anonymity', EnumType::class, [
                'class' => AnonymityType::class,
                'expanded' => true,
                'multiple' => false,
                'choice_label' => function (
                    $choice,
                    string $key,
                    mixed $value
                ) {
                    return $choice->getDescriptor();
                },
                'help' => 'Whether or not votes for this election should be fully, semi, or not anonymized.'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Create Election',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr' => [
                'id' => 'electionForm'
            ]
        ]);
    }
}
