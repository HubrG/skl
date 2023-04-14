<?php

namespace App\Controller\Admin;

use App\Entity\UserParameters;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserParametersCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserParameters::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Utilisateur')->setSortable(true),
            BooleanField::new('darkmode', 'Darkmode'),
            BooleanField::new('notif1Mail', 'Com. récit mail'),
            BooleanField::new('notif1Web', 'Com. récit web'),
            BooleanField::new('notif2Mail', 'Com. feuille mail'),
            BooleanField::new('notif2Web', 'Com. feuille web'),
            BooleanField::new('notif3Mail', 'Like com. mail'),
            BooleanField::new('notif3Web', 'Like com. web'),
            BooleanField::new('notif4Mail', 'Add collection mail'),
            BooleanField::new('notif4Web', 'Add collection web'),
            BooleanField::new('notif5Mail', 'New dl mail'),
            BooleanField::new('notif5Web', 'New dl web'),
            BooleanField::new('notif6Mail', 'Like chap. mail'),
            BooleanField::new('notif6Web', 'Like chap. web'),
            BooleanField::new('notif7Mail', 'New chap. mail'),
            BooleanField::new('notif7Web', 'New chap. web'),
            BooleanField::new('notif8Mail', 'New feed mail'),
            BooleanField::new('notif8Web', 'New feed web'),
            BooleanField::new('notif9Mail', 'Resp. com. mail'),
            BooleanField::new('notif9Web', 'Resp. com. web'),


        ];
    }
}
