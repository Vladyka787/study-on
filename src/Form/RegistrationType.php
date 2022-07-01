<?php

namespace App\Form;

use App\Security\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'label' => 'Имейл',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите пароль.',
                    ]),
                ],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Пароль',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите пароль.',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Пароль содержит минимум 6 символов.',
                    ]),
                ],
            ])
            ->add('passwordRepeat', PasswordType::class, [
                'label' => 'Повторите пароль',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Повторите пароль.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
