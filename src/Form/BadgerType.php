<?php

namespace App\Form;

use App\Entity\Badger\BadgerRequest;
use App\Entity\Badger\Species\Human;
use App\Enum\Badger\CardBackgrounds;
use App\Enum\Badger\Directions;
use App\Enum\Badger\IDCards;
use App\Factory\SpeciesFactory;
use App\Form\DataTransformer\SpeciesTransformer;
use App\Form\Type\ChoiceToArrayType;
use App\Repository\ManifestRepository;
use App\Service\Icons\IconListService;
use App\Service\Species\SpeciesClassRegistry;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BadgerType extends AbstractType
{
    public function __construct(
        private SpeciesClassRegistry $speciesClassRegistry,
        private IconListService $iconListService,
        private SpeciesFactory $speciesFactory,
        private Security $security,
        private ManifestRepository $manifestRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('species', ChoiceType::class, [
                'choices' => $this->speciesClassRegistry->getSpeciesClasses(),
                'choice_label' => fn($key, $value, $choice) => $value,
                'help' => 'The species you are generating'
            ])
            ->get('species')
            ->addModelTransformer(new SpeciesTransformer($this->speciesFactory));
        $builder
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female'
                ]
            ])
            ->add('direction', EnumType::class, [
                'label' => 'Facing',
                'class' => Directions::class,
                'choice_label' => function (
                    $choice,
                    string $key,
                    mixed $value
                ) {
                    return ucfirst(strtolower($choice->name));
                }
            ])
            ->add('skinTone', ColorType::class, [
                'required' => false,
                'empty_data' => null
            ])
            ->add('humanSkinTone', ChoiceType::class, [
                'placeholder' => false,
                'mapped' => false,
                'choices' => Human::SKINTONES,
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'choice_label' => function (
                    $choice,
                    string $key,
                    mixed $value
                ) {
                    return false;
                },
                'choice_attr' => function ($choice, string $key, mixed $value) {
                    return [
                        'style' => 'background: ' . $choice,
                        'class' => 'skintone-selector',
                        'data-color' => $choice
                    ];
                },
                'label' => 'Skintone',
                'attr' => [
                    'class' => 'visually-hidden'
                ]
            ])
            ->add('eyeColor', ColorType::class)
            ->add('hairColor', ColorType::class)
            ->add('cardBackground', EnumType::class, [
                'label' => 'Corporate Card Background',
                'class' => CardBackgrounds::class,
                'choice_label' => function (
                    $choice,
                    string $key,
                    mixed $value
                ) {
                    return ucfirst(strtolower($choice->name));
                }
            ])
            ->add('stationId', EnumType::class, [
                'label' => 'Station ID',
                'class' => IDCards::class,
                'choice_label' => function (
                    $choice,
                    string $key,
                    mixed $value
                ) {
                    return ucfirst(strtolower($choice->name));
                }
            ])
            ->add('name', TextType::class, [
                'data' => 'A. Spaceman'
            ])
            ->add('job', TextType::class, [
                'data' => 'Assistant'
            ])
            ->add('bottomText', TextType::class, [
                'data' => 'Bottom Text'
            ])
            ->add('generate', SubmitType::class)
            ->add('undersuit', ChoiceType::class, [
                'label' => 'Uniform',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/under'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('ears', ChoiceType::class, [
                'label' => 'Ears',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/ears'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('mask', ChoiceType::class, [
                'label' => 'Mask',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/mask'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('helmet', ChoiceType::class, [
                'label' => 'Headwear',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/head'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('suit', ChoiceType::class, [
                'label' => 'Exosuit',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/suits'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('belt', ChoiceType::class, [
                'label' => 'Belt',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/belt'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('eye', ChoiceType::class, [
                'label' => 'Eye Wear',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/eyes'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('glove', ChoiceType::class, [
                'label' => 'Gloves',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/hands'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('foot', ChoiceType::class, [
                'label' => 'Shoes',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/feet'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('back', ChoiceType::class, [
                'label' => 'Back',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/back'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('neck', ChoiceType::class, [
                'label' => 'Neck',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/neck'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('underwear', ChoiceType::class, [
                'label' => 'Underwear',
                'choices' => $this->iconListService->listIcons(
                    '/mob/clothing/underwear'
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('hud', ChoiceType::class, [
                'label' => 'HUD Icon',
                'choices' => $this->iconListService->listIcons('/mob/huds'),
                'multiple' => true,
                'autocomplete' => true,
                'required' => false,
                'help' => 'You can select multiple icons!'
            ])
            ->add('augment', ChoiceType::class, [
                'label' => 'Augments',
                'choices' => $this->iconListService->listIcons(
                    '/mob/augmentation'
                ),
                'multiple' => true,
                'autocomplete' => true,
                'required' => false,
                'help' => 'You can select multiple augments!'
            ])
            ->add('hair', ChoiceType::class, [
                'label' => 'Hair Style',
                'choices' => $this->iconListService->listIcons(
                    '/mob/human/human_face',
                    ['hair', 'debrained', 'bald']
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('facial', ChoiceType::class, [
                'label' => 'Facial Hair Style',
                'choices' => $this->iconListService->listIcons(
                    '/mob/human/human_face',
                    ['facial']
                ),
                'autocomplete' => true,
                'required' => false
            ])
            ->add('facialColor', ColorType::class, [
                'label' => 'Facial Hair Color'
            ])
            ->add('holding', ChoiceType::class, [
                'label' => 'Holding',
                'choices' => $this->iconListService->listIcons('/mob/inhands'),
                'autocomplete' => true,
                'required' => false,
                'multiple' => true,
                'help' => 'You can select multiple items!'
            ]);
        //Assign icon to character, requires a currently logged in user
        if ($this->security->getUser()) {
            $builder->add('assign', ChoiceType::class, [
                'mapped' => false,
                'choices' => array_column(
                    $this->manifestRepository->fetchPlayerCharacters(
                        $this->security->getUser()->getCkey()
                    ),
                    'character'
                ),
                'choice_label' => function ($key, $value, $choice) {
                    return $choice;
                },
                'required' => false,
                'help' => 'Select a character to assign this image to'
            ])->add('assignBtn', SubmitType::class, [
                'label' => 'Generate and Assign',
                'attr' => [
                    'formaction' => $this->urlGenerator->generate(
                        'badger.generate.assign'
                    ),
                    'class' => 'disabled btn btn-primary'
                ]
            ]);
        }

        //Moth parts
        $builder->add('mothWings', ChoiceToArrayType::class, [
            'label' => 'Moth Wings',
            'choices' => $this->iconListService->listIcons(
                '/mob/human/species/moth/moth_wings/'
            ),
            'autocomplete' => true,
            'required' => false,
            'row_attr' => [
                'class' => 'col visually-hidden',
                'data-for-species' => 'Moth'
            ],
            'mapped' => false
        ])->add('mothAntennae', ChoiceToArrayType::class, [
            'label' => 'Moth Antennae',
            'choices' => $this->iconListService->listIcons(
                '/mob/human/species/moth/moth_antennae/'
            ),
            'autocomplete' => true,
            'required' => false,
            'row_attr' => [
                'class' => 'col visually-hidden',
                'data-for-species' => 'Moth'
            ],
            'mapped' => false
        ])->add('mothMarkings', ChoiceToArrayType::class, [
            'label' => 'Moth Markings',
            'choices' => $this->iconListService->listIcons(
                '/mob/human/species/moth/moth_markings/'
            ),
            'autocomplete' => true,
            'required' => false,
            'row_attr' => [
                'class' => 'col visually-hidden',
                'data-for-species' => 'Moth'
            ],
            'multiple' => true,
            'mapped' => false
        ]);

        //Felinid parts
        $builder->add('felinidTail', ChoiceType::class, [
            'label' => 'Felinid Features',
            'choices' => $this->iconListService->listIcons(
                '/mob/human/cat_features/'
            ),
            'autocomplete' => true,
            'required' => false,
            'row_attr' => [
                'class' => 'col visually-hidden',
                'data-for-species' => 'Felinid'
            ],
            'multiple' => true,
            'mapped' => false
        ]);

        $builder->addEventListener(
            FormEvents::SUBMIT,
            function (FormEvent $formEvent) {
                $form = $formEvent->getForm();
                $data = $form->getData();
                foreach ($form as $child) {
                    if (!$child->getConfig()->getMapped()) {
                        $input = $child->getData();
                        if (is_string($input)) {
                            $data->extras[$child->getName()][] =
                                $child->getData();
                        } elseif (is_array($input)) {
                            $data->extras[$child->getName()] =
                                $child->getData();
                        } else {
                            continue;
                        }
                    }
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgerRequest::class,
            'attr' => ['id' => 'generator', 'autocomplete' => 'off']
        ]);
    }
}
