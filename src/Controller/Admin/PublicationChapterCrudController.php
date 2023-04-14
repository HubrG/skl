<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapter;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapter::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('publication', 'Publication référente'),
            TextField::new('title', "Titre"),
            TextEditorField::new('content', "Contenu")->setNumOfRows(50),
        ];
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des chapitres de publication')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détails du chapitre de publication')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le chapitre de publication')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un chapitre de publication')
            ->setEntityLabelInSingular('un nouveau chapitre')
            ->setEntityLabelInPlural('un chapitre')
            ->setSearchFields(['id', 'title', 'content'])
            ->setDefaultSort(['id' => 'DESC']);
    }
}
