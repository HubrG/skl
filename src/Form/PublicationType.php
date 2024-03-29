<?php

namespace App\Form;

use App\Entity\Publication;
use Doctrine\ORM\EntityRepository;
use App\Entity\PublicationCategory;
use App\Form\UserAutocompleteField;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PublicationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => false,
                'label' => "Quel est le titre de votre récit ?",
                // 'attr' => array(
                //     'placeholder' => 'Quel est le titre de cette histoire ?'
                // )
            ])
            ->add('summary', TextareaType::class, [
                'required' => false,
                'label' => "Pourriez-vous le résumer en quelques lignes ?",
            ])
            ->add('cover', FileType::class, [
                'mapped' => false,
                'required' => false,

            ])
            ->add('mature', CheckboxType::class, [
                'required' => false
            ])
            ->add('category', EntityType::class, [
                'placeholder' => "Choisir une catégorie",
                "class" => PublicationCategory::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->orderBy('u.name', 'ASC');
                },
                'label' => "Dans quelle catégorie rangeriez-vous ce récit ?",
                'choice_label' => 'name',
                "required" => false
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Publication::class
        ]);
    }
}
