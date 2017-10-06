<?php

namespace AppBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateUserType extends UserType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'is_edit' => true,
        ]);
    }

    public function getBlockPrefix()
    {
        return 'user_edit';
    }
}
