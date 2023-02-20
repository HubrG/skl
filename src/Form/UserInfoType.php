<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
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
                'label' => 'Quel est votre nom d\'auteur ?',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez entrer un nom',
                    ])
                ]
            ])
            ->add('about', TextareaType::class, [
                'label' => 'Comment vous présenteriez-vous ?',
                'required' => false
            ])
            ->add('facebook', UrlType::class, [
                'label' => 'Collez ici l\'adresse de votre profil Facebook',
                'required' => false
            ])
            ->add('twitter', UrlType::class, [
                'label' => 'Collez ici l\'adresse de votre profil Twitter',
                'required' => false
            ])
            ->add('website', UrlType::class, [
                'label' => 'Collez ici l\'adresse de votre site web personnel',
                'required' => false
            ])
            ->add('instagram', UrlType::class, [
                'label' => 'Collez ici l\'adresse de votre profil Instagram',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
