<?php

namespace App\Controller\Admin;

use App\Entity\Song;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Validator\Constraints\File;
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
                ->setBasePath('/uploads/songs')
                ->setUploadDir('public/uploads/songs')
                ->setRequired(false)
                ->setFormTypeOptions([
                    'constraints' => [
                        new File(
                    maxSize: '20M',
                    mimeTypes: [
                    'audio/mpeg',
                    'audio/mp3',
                    'audio/wav',
                    ]
                )
                    ],
                ]),

            FileField::new('mp3File', 'Fichier audio (.mp3)')
    ->setUploadDir('public/uploads/songs')
    ->setBasePath('/uploads/songs')
    ->setRequired(false)
    ->setFormTypeOptions([
        'mapped' => false,
        'constraints' => [
            new FileConstraint(
                maxSize: '20M',
                mimeTypes: ['audio/mpeg', 'audio/mp3']
            )
        ],
    ])
    ->hideOnIndex(),
    
        ];
    }
}
