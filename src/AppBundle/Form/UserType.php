<?php

namespace AppBundle\Form;

use AppBundle\Entity\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'disabled' => $options['is_edit'],
                'label' => 'username',
            ])
            ->add('email', EmailType::class, [
                'label' => 'email',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'enabled',
                'required' => false,
            ])
            ->add('groups', EntityType::class, [
                'class' => Group::class,
                'choice_label' => function($v) {
                    return $v->getName();
                },
                'label' => 'groups',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'data_class' => 'AppBundle\Entity\User',
            'is_edit' => false,
            'translation_domain' => 'user',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user';
    }
}
