<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface as FormFormBuilderInterface;

class ChoiceToArrayType extends AbstractType
{
    public function buildForm(FormFormBuilderInterface $builder, array $options)
    {
        $multiple = $options['multiple'];

        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($value) use ($multiple) {
                    if ($multiple) {
                        return $value ?? [];
                    }
                    return is_array($value) ? reset($value) : $value;
                },
                function ($value) use ($multiple) {
                    if ($value === null) {
                        return [];
                    }

                    if ($multiple) {
                        return $value;
                    }
                    return [$value];
                }
            )
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'choice_array';
    }
}
