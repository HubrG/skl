<?php

namespace App\Controller\Admin;

use App\Entity\Publication;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Publication::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('category', 'Catégorie')->setSortable(true),
            TextField::new('title', "Titre")->formatValue(function ($value, $entity) {
                return "<strong>" . htmlspecialchars_decode($value) . "</strong>";
            }),
            AssociationField::new('user', 'Auteur'),
            TextEditorField::new('summary'),
            IntegerField::new('status', "Publié")->formatValue(function ($value, $entity) {
                if ($value == 1) {
                    return "<span class='badge badge-success'>Oui</span>";
                } elseif ($value == 0) {
                    return "<span class='badge badge-warning'>En attente</span>";
                } else {
                    return "<span class='badge badge-danger'>Non</span>";
                }
            }),
            TextField::new('slug', "Slug"),

        ];
    }

    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Récits')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du chapitre de publication')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le chapitre de publication')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un chapitre de publication')
            ->setEntityLabelInSingular('un nouveau chapitre')
            ->setEntityLabelInPlural('un chapitre')
            ->setSearchFields(['id', 'title', 'summary', 'user.username', 'status', 'created', 'updated'])
            ->setDefaultSort(['id' => 'DESC']);
    }
}
