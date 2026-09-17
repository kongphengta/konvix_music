<?php

namespace App\Controller\Admin;

use App\Entity\Artist;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

final class ArtistAdminCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Artist::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('artiste')
            ->setEntityLabelInPlural('artistes')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name')->setLabel('Nom de l’artiste'),
            TextField::new('slug')->setLabel('Slug'),
            TextField::new('genre')->setLabel('Genre musical'),
            TextField::new('city')->setLabel('Ville'),
            TextField::new('followers')->setLabel('Followers'),
            TextareaField::new('bio')->setLabel('Biographie'),
            AssociationField::new('user')->setLabel('Utilisateur associé'),
            ChoiceField::new('color')->setLabel('Couleur du branding')->setChoices([
                'Violet' => 'violet',
                'Cyan' => 'cyan',
                'Rose' => 'pink',
                'Or' => 'gold',
                'Rose pâle' => 'rose',
                'Vert' => 'green',
            ]),
            BooleanField::new('isPublished')->setLabel('Publié'),
        ];
    }
}
