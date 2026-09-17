<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

final class UserAdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('utilisateur')
            ->setEntityLabelInPlural('utilisateurs')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('firstName')->setLabel('Prénom'),
            TextField::new('lastName')->setLabel('Nom'),
            EmailField::new('email'),
            ChoiceField::new('accountType')
                ->setLabel('Type de compte')
                ->setChoices([
                    'Artiste' => 'artist',
                    'Auditeur' => 'auditeur',
                    'Administrateur' => 'admin',
                ]),
            ChoiceField::new('roles')
                ->setLabel('Rôles')
                ->setChoices([
                    'Admin' => 'ROLE_ADMIN',
                    'Auditeur' => 'ROLE_AUDITEUR',
                    'Artiste' => 'ROLE_ARTISTE',
                    'Utilisateur' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            BooleanField::new('isVerified')->setLabel('Email vérifié'),
            TextField::new('password')->onlyOnForms()->setLabel('Mot de passe'),
        ];
    }
}
