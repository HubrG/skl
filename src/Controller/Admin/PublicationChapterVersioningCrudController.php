<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapterVersioning;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterVersioningCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapterVersioning::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
