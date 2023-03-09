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
                'label' => 'Votre nom d\'auteur ?',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez entrer un nom',
                    ])
                ]
            ])
            ->add('about', TextareaType::class, [
                'label' => 'Présentation',
                'required' => false
            ])
            ->add('facebook', UrlType::class, [
                'label' => 'Adresse de votre profil Facebook',
                'required' => false
            ])
            ->add('twitter', UrlType::class, [
                'label' => 'Adresse de votre profil Twitter',
                'required' => false
            ])
            ->add('website', UrlType::class, [
                'label' => 'Adresse de votre site web personnel',
                'required' => false
            ])
            ->add('instagram', UrlType::class, [
                'label' => 'Adresse de votre profil  Instagram',
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
