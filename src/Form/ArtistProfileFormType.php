<?php

namespace App\Form;

use App\Entity\ArtistProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ArtistProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('stageName', TextType::class, [
                'label' => 'Nom de scène *',
                'attr'  => ['placeholder' => 'Ex : DJ Konvix, The Wavs…'],
                'constraints' => [
                    new NotBlank(message: 'Le nom de scène est obligatoire.'),
                    new Length(min: 2, max: 150),
                ],
            ])
            ->add('genre', TextType::class, [
                'label'    => 'Genre musical *',
                'attr'     => ['placeholder' => 'Ex : Country, Jazz, Afrobeat…'],
                'constraints' => [new NotBlank(message: 'Indiquez votre genre musical.')],
            ])
            ->add('bio', TextareaType::class, [
                'label'    => 'Biographie',
                'required' => false,
                'attr'     => ['rows' => 4, 'placeholder' => 'Parlez de vous, votre style, votre histoire…'],
            ])
            ->add('country', TextType::class, [
                'label'    => 'Pays',
                'required' => false,
                'attr'     => ['placeholder' => 'France, Sénégal, Canada…'],
            ])
            ->add('website', UrlType::class, [
                'label'       => 'Site web / lien',
                'required'    => false,
                'default_protocol' => 'https',
                'attr'        => ['placeholder' => 'https://monsite.com'],
            ])
            ->add('photoFile', VichImageType::class, [
                'label'         => 'Photo de profil',
                'required'      => false,
                'allow_delete'  => false,
                'download_uri'  => false,
                'constraints'   => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Formats acceptés : JPG, PNG, WebP.'
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ArtistProfile::class]);
    }
}
