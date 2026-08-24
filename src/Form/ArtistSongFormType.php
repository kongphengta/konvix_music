<?php

namespace App\Form;

use App\Entity\Album;
use App\Entity\Category;
use App\Entity\Song;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ArtistSongFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du morceau *',
                'attr'  => ['placeholder' => 'Ex : Hand In Mine'],
                'constraints' => [new NotBlank(message: 'Le titre est obligatoire.')],
            ])
            ->add('category', EntityType::class, [
                'label'        => 'Genre / Catégorie *',
                'class'        => Category::class,
                'choice_label' => 'name',
                'placeholder'  => '-- Choisir une catégorie --',
                'constraints'  => [new NotBlank(message: 'Choisissez une catégorie.')],
            ])
            ->add('album', EntityType::class, [
                'label'        => 'Album',
                'class'        => Album::class,
                'choice_label' => 'title',
                'required'     => false,
                'placeholder'  => '-- Aucun album --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
            'require_uploads' => false,
        ]);

        $resolver->setAllowedTypes('require_uploads', 'bool');
    }
}
