<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapterNote;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterNoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapterNote::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            AssociationField::new('user', 'Utilisateur'),
            AssociationField::new('chapter', 'Chapitre'),
            TextEditorField::new('selection', 'Selection'),
        ];
    }
    public function configureCrud(Crud $crud): Crud
    {

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Notes personnelles des utilisateurs sur les chapitres')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du chapitre de publication')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le chapitre de publication')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un chapitre de publication')
            ->setEntityLabelInSingular('un nouveau chapitre')
            ->setEntityLabelInPlural('un chapitre')
            ->setDefaultSort(['id' => 'DESC']);
    }
}
