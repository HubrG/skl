<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
class UserInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nickname', TextType::class, [
            'required' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Vous devez entrer un nom',
                ])]])
            ->add('about', TextareaType::class, [
                'required' => false
                ])
                ->add('city', TextType::class, [
                    'required' => false
                    ])
                    ->add('profil_picture', FileType::class, [
                        'required' => false,
                        'mapped' => false,
                        'data_class' => null
                        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
