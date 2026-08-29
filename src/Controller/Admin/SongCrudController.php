<?php

namespace App\Controller\Admin;

use App\Entity\Song;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FileField;
use Symfony\Component\Validator\Constraints\File as FileConstraint;


class SongCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Song::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title', 'Titre'),

            SlugField::new('slug')
                ->setTargetFieldName('title')
                ->hideOnIndex(),

            AssociationField::new('category', 'Catégorie'),
            AssociationField::new('album', 'Album'),

            ImageField::new('image', 'Image')
                ->setBasePath('/uploads/covers')
                ->setUploadDir('public/uploads/covers')
                ->setRequired(false)
                ->setFileConstraints([
                    new FileConstraint(
                        maxSize: '20M',
                        mimeTypes: [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        mimeTypesMessage: 'Formats acceptés : JPG, PNG, WebP.'
                    ),
                ]),

            FileField::new('filename', 'Fichier audio (.mp3 / .wav)')
                ->setUploadDir('public/uploads/songs')
                ->setBasePath('/uploads/songs')
                ->setRequired(false)
                ->setFileConstraints([
                    new FileConstraint(
                        maxSize: '20M',
                        mimeTypes: [
                            'audio/mpeg',
                            'audio/mp3',
                            'audio/wav',
                            'audio/x-wav',
                        ],
                        mimeTypesMessage: 'Formats acceptés : MP3 ou WAV.'
                    ),
                ])
                ->hideOnIndex(),
        ];
    }
}
