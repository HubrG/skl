<?php

namespace App\Controller\Admin;

use App\Entity\PublicationChapterLike;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PublicationChapterLikeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PublicationChapterLike::class;
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
