<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class AlbumCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Album::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),

            TextField::new('title', 'Titre'),

            SlugField::new('slug')
                ->setTargetFieldName('title')
                ->hideOnIndex(),

            ImageField::new('cover', 'Image de l\'album')
                ->setBasePath('/uploads/albums')
                ->setUploadDir('public/uploads/albums')
                ->setRequired(false),

            AssociationField::new('songs', 'Chansons associées')
                ->setFormTypeOptions([
                    'by_reference' => false,
                ]),
        ];
    }
}
