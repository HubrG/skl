<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Inbox;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class InboxNewMessageType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Votre message',
                    'rows' => 5,
                ],
            ])
            ->add('UserTo', EntityType::class, [
                'label' => false,
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getUsername() . ' ' . $user->getNickname();
                },
                'placeholder' => 'Sélectionnez un utilisateur',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Inbox::class,
        ]);
    }
}
