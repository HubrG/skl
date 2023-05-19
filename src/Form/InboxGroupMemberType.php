<?php

namespace App\Form;

use App\Entity\InboxGroupMember;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InboxGroupMemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserAutocompleteField::class, [
                'label' => false,
                'multiple' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Choisissez un ou plusieurs utilisateurs',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InboxGroupMember::class,
        ]);
    }
}
