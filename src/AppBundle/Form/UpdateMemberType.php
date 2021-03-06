<?php

namespace AppBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateMemberType extends MemberType
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
        return 'member_edit';
    }
}
