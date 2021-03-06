<?php

namespace Msi\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraint\UserPassword as OldUserPassword;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

use FOS\UserBundle\Form\Type\ProfileFormType as BaseType;

class ProfileType extends BaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (class_exists('Symfony\Component\Security\Core\Validator\Constraints\UserPassword')) {
            $constraint = new UserPassword();
        } else {
            // Symfony 2.1 support with the old constraint class
            $constraint = new OldUserPassword();
        }

        $this->buildUserForm($builder, $options);

        $builder->add('current_password', 'password', array(
            'label' => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => $constraint,
            'attr' => [
                'class' => 'form-control',
            ],
        ));
    }

    protected function buildUserForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('username', null, array('label' => 'form.username', 'translation_domain' => 'FOSUserBundle'))
            ->add('email', 'email', array('attr' => ['class' => 'form-control'], 'label' => 'form.email', 'translation_domain' => 'FOSUserBundle'))
            ->add('locale', 'choice', [
                'empty_value' => 'Default',
                'empty_data' => null,
                'choices' => [
                    'fr' => 'FR',
                    'en' => 'EN',
                ],
                'label' => 'Language',
                'required'    => false,
                'attr' => [
                    'class' => 'notchosen form-control',
                ],
            ])
        ;
    }

    public function getName()
    {
        return 'msi_user_profile';
    }
}
