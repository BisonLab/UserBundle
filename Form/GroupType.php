<?php

namespace BisonLab\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use BisonLab\UserBundle\Entity\Group;
use BisonLab\UserBundle\Lib\ExternalEntityConfig;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('roles', ChoiceType::class, [
                'required' => true,
                'multiple' => true,
                'choices' => ExternalEntityConfig::getRolesAsChoices(),
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Group::class,
        ]);
    }
}
