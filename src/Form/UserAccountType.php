<?php

namespace App\Form;

use App\Entity\User;
use Geolocation\Address;
use Geolocation\Geolocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class UserAccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'constraints' => [],
            ])
            ->add('username', TextType::class, [
                'label' => 'Nom d\'utilisateur',
                // Nom d'utilisateur doit être unique
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]$/',
                        'message' => "Nom d'utilisateur invalide. Caractères autorisés : lettres, chiffres, trait d'union. Ne peut pas commencer ou se terminer par un trait d'union.",
                    ]),
                    // new UniqueEntity([
                    //     'fields' => ['username'],
                    //     'message' => 'Ce nom d\'utilisateur est déjà utilisé',
                    // ]),
                ],
            ])
            ->add('city',  TextType::class, [
                'label' => 'Ville',
                'required' => false,

            ])
            // ->add('profil_picture')
            ->add('birth', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'label' => 'Date de naissance',
            ])
            // ->add('profil_background')
            ->add('country', CountryType::class, [
                'label' => 'Pays',
                'required' => false,
                'preferred_choices' => ['FR'],
            ]);
        // ->add('join_date');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
