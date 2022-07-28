<?php

namespace App\Form;

use App\Entity\Lesson;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\DataTransformer\CourseToNumberTransformer;

class LessonType extends AbstractType
{
    private $transformer;

    public function __construct(CourseToNumberTransformer $transformer)
    {
        $this->transformer = $transformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'LessonName',
                TextType::class,
                [
                    'label' => 'Название урока'
                ]
            )
            ->add(
                'LessonContent',
                TextareaType::class,
                [
                    'label' => 'Контент урока'
                ]
            )
            ->add(
                'LessonNumber',
                NumberType::class,
                [
                    'label' => 'Номер урока',
                    'invalid_message' => 'Введите корректные данные.',
                ]
            )
            ->add(
                'Course',
                HiddenType::class,
                ['data' => $options['course_id'], 'mapped' => false]
            );

        $builder->get('Course')
            ->addModelTransformer($this->transformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lesson::class,
            'course_id' => 0,
        ]);
    }
}
