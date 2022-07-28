<?php

namespace App\Form;

use App\Entity\Course;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'CourseName',
                TextType::class,
                [
                    'label' => 'Название курса'
                ]
            )
            ->add(
                'CharacterCode',
                TextType::class,
                [
                    'label' => 'Код курса'
                ]
            )
            ->add(
                'CourseDescription',
                TextareaType::class,
                [
                    'label' => 'Описание курса'
                ]
            )
            ->add(
                'Price',
                NumberType::class,
                [
                    'label' => 'Стоимость курса',
                    'mapped' => false,
                    'data' => $options['price'],
                    'invalid_message' => 'Введите корректные данные.',
                ]
            )
            ->add(
                'Type',
                ChoiceType::class,
                [
                    'label' => 'Тип курса',
                    'mapped' => false,
                    'data' => $options['type'],
                    'choices' => [
                        'Аренда' => 'rent',
                        'Полный' => 'full',
                    ],
                    'preferred_choices' => function ($choice, $key, $value) use ($options) {
                        if ($options['type'] === 'rent') {
                            return $choice == 'rent';
                        }

                        return $choice == 'full';
                    },
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
            'type' => null,
            'price' => null,
        ]);
    }
}
