<?php

namespace App\Form;

use App\Entity\ChallengeMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ChallengeMessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => 'Commentaire sur l\'exercice',
                'attr' => [
                    'rows' => 10,
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez remplir ce champ.',
                    ]),
                ],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChallengeMessage::class,
        ]);
    }
}
