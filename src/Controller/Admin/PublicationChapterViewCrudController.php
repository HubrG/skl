<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapterView;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterViewCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapterView::class;
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
