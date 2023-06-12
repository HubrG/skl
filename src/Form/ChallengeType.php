<?php

namespace App\Form;

use DateTime;
use DateTimeImmutable;
use App\Entity\Challenge;
use Doctrine\ORM\EntityRepository;
use App\Entity\PublicationCategory;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ChallengeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (isset($options['data'])) {
            $entity = $options['data'];
            $dateStart = $entity && $entity->getDateStart() ? $entity->getDateStart() : new DateTime('now');
        } else {
            $dateStart = new DateTime('now');
        }


        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de l\'exercice',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner le titre de l\'exercice',
                    ]),
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Énoncé de l\'exercice',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner l\'énoncé de l\'exercice.',
                    ]),
                ],
            ])
            ->add('dateStart', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'data' => $dateStart,
                'placeholder' => 'JJ/MM/AAAA',
                'label' => 'Début de l\'exercice',
            ])
            ->add('dateEnd', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
                'html5' => true,
                'label' => 'Fin de l\'exercice',
                'placeholder' => 'JJ/MM/AAAA',
            ])
            ->add('constrainMinTime', NumberType::class, [
                'label' => 'Temps de lecture minimum',
                'required' => false,
                "help" => "Exprimé en minutes de lecture",
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainMaxTime', NumberType::class, [
                'label' => 'Temps de lecture maximum',
                'required' => false,
                "help" => "Exprimé en minutes de lecture",
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainMaxWords', NumberType::class, [
                'label' => 'Maximum de mots',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainMinLetters', NumberType::class, [
                'label' => 'Minimum de signes',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainMaxLetters', NumberType::class, [
                'label' => 'Maximum de signes',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainMinWords', NumberType::class, [
                'label' => 'Minimum de mots',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Vous ne pouvez pas entrer une valeur négative.',
                    ]),
                ],
            ])
            ->add('constrainCategory', EntityType::class, [
                'placeholder' => "Choisir un thème",
                "class" => PublicationCategory::class,
                'query_builder' => function (EntityRepository $em) {
                    return $em->createQueryBuilder('c')
                        ->orderBy('c.name', 'ASC');
                },
                'label' => "Thème imposé",
                'choice_label' => 'name',
                "required" => false
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $challenge = $event->getData();

            if ($challenge->getConstrainMaxTime() && ($challenge->getConstrainMinTime() > $challenge->getConstrainMaxTime())) {
                $form->get('constrainMinTime')->addError(new FormError('Le temps de lecture minimum ne peut pas être supérieur au temps de lecture maximum.'));
            }
            if ($challenge->getConstrainMaxWords() && ($challenge->getConstrainMinWords() > $challenge->getConstrainMaxWords())) {
                $form->get('constrainMinWords')->addError(new FormError('Le nombre de mots minimum ne peut pas être supérieur au nombre de mots maximum.'));
            }
            if ($challenge->getConstrainMaxLetters() && ($challenge->getConstrainMinLetters() > $challenge->getConstrainMaxLetters())) {
                $form->get('constrainMinLetters')->addError(new FormError('Le nom de lettres minimum ne peut pas être supérieur au nombre de lettres maximum.'));
            }
            if ($challenge->getDateEnd() && ($challenge->getDateStart() > $challenge->getDateEnd())) {
                $form->get('dateStart')->addError(new FormError('La date du début de l\'exercice ne peut pas être supérieure à la date de fin de l\'exercice.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Challenge::class,
        ]);
    }
}
