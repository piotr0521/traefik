<?php

declare(strict_types=1);

namespace Groshy\Presentation\Web\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;

final class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, ['label' => 'talav.form.email', 'translation_domain' => 'TalavUserBundle'])
            ->add('username', null, ['label' => 'talav.form.username', 'translation_domain' => 'TalavUserBundle'])
            ->add('firstName', null, ['label' => 'First Name'])
            ->add('lastName', null, ['label' => 'Last Name'])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => [
                    'translation_domain' => 'TalavUserBundle',
                    'attr' => [
                        'autocomplete' => 'new-password',
                    ],
                ],
                'first_options' => ['label' => 'talav.form.password'],
                'second_options' => ['label' => 'talav.form.password_confirmation'],
                'invalid_message' => 'talav.password.mismatch',
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'groshy_user_registration';
    }
}
