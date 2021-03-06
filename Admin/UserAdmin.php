<?php

namespace Msi\UserBundle\Admin;

use Msi\AdminBundle\Admin\Admin;
use Msi\AdminBundle\Grid\GridBuilder;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;

class UserAdmin extends Admin
{
    public function configure()
    {
        $this->options = [
            'search_fields' => ['a.id', 'a.username', 'a.email'],
            'form_template' => 'MsiUserBundle:User:form.html.twig',
            'sidebar_template' => 'MsiUserBundle:User:sidebar.html.twig',
        ];
    }

    public function buildGrid(GridBuilder $builder)
    {
        $builder
            ->add('locked', 'boolean', [
                'icon_true' => 'icon-ban-circle',
                'icon_false' => 'icon-ban-circle',
                'btn_true' => 'label-danger',
            ])
            ->add('email')
            ->add('lastLogin', 'date')
        ;
    }

    public function buildForm(FormBuilder $builder)
    {
        $builder
            ->add('email')
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Confirm Password'],
            ])
            ->add('locale', 'choice', [
                'empty_value' => 'Default',
                'empty_data' => null,
                'choices' => [
                    'fr' => 'FR',
                    'en' => 'EN',
                ],
                'label' => 'Language',
                'required'    => false,
            ])
            ->add('locked', 'checkbox')
        ;

        $builder->add('groups', 'entity', [
            'class' => 'MsiUserBundle:Group',
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'query_builder' => function(EntityRepository $er) {
                $qb = $er->createQueryBuilder('a')->addOrderBy('a.name', 'ASC');

                if (!$this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
                    $qb->andWhere($qb->expr()->neq('a.name', ':tokenblabla'))->setParameter('tokenblabla', 'super admin');
                }

                return $qb;
            },
        ]);

        if ($this->container->get('security.context')->isGranted('ROLE_SUPER_ADMIN')) {
            // $roles = $this->container->getParameter('msi_user.roles');

            // foreach ($this->container->getParameter('msi_admin.admin_ids') as $id) {
            //     $label = $this->container->get($id)->getLabel(1, 'en');
            //     $roles['ROLE_'.strtoupper($id).'_CREATE'] = $label.' | create';
            //     $roles['ROLE_'.strtoupper($id).'_READ'] = $label.' | read';
            //     $roles['ROLE_'.strtoupper($id).'_UPDATE'] = $label.' | update';
            //     $roles['ROLE_'.strtoupper($id).'_DELETE'] = $label.' | delete';
            // }

            // $builder
            //     ->add('roles', 'choice', [
            //         'choices' => $roles,
            //         'expanded' => true,
            //         'multiple' => true,
            //         'required' => false,
            //     ])
            // ;

            $builder->add('operators', 'entity', [
                'class' => 'MsiUserBundle:Group',
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function prePersist($entity)
    {
        $entity->setEnabled(true);
    }

    public function buildListQuery(QueryBuilder $qb)
    {
        $qb->addOrderBy('a.username', 'ASC');
    }

    public function postLoad(ArrayCollection $collection)
    {
        $this->container->get('msi_admin.bouncer')->operatorFilter($collection);
    }
}
