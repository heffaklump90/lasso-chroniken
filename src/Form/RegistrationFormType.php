<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email',
            ])
            ->add('firstName', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Vorname',
            ])
            ->add('lastName', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nachname',
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'attr' => ['class' => 'form-check'],
                'label' => 'Einwilligung zur speicherung Ihrer Daten (Vorname, Name, Email)',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Sie müssen zustimmen um sich registrieren zu können',
                    ]),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'options' => ['attr'=> ['class' => 'form-control']],
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
                'invalid_message' => 'Die Passwörter stimmen nicht überein',
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Bitte ein Passwort eingeben',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Ihr Passwort sollte zumindest {{ limit }} Zeichen enthalten',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-dark mt-2'],
                'label' => 'Registrieren',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
