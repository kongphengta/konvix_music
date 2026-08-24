<?php

namespace App\Controller\Admin;

use App\Entity\ArtistProfile;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ArtistCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ArtistProfile::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('stageName', 'Nom de scène'),
            SlugField::new('slug')->setTargetFieldName('stageName')->hideOnIndex(),
            TextField::new('genre', 'Genre musical'),
            TextField::new('country', 'Pays')->hideOnIndex(),
            ImageField::new('photo', 'Photo')
                ->setBasePath('/uploads/artists')
                ->setUploadDir('public/uploads/artists')
                ->setRequired(false)
                ->hideOnIndex(),
            TextareaField::new('bio', 'Biographie')->hideOnIndex(),
            BooleanField::new('isApproved', 'Validé'),
            AssociationField::new('user', 'Compte utilisateur')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Inscrit le')->hideOnForm(),
        ];
    }
}
