<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapterNote;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterNoteCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapterNote::class;
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
