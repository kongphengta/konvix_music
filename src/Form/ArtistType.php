<?php

namespace App\Form;

use App\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('slug', TextType::class, [
                'label' => 'Slug',
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('genre', TextType::class, [
                'label' => 'Genre',
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
            ])
            ->add('followers', TextType::class, [
                'label' => 'Followers',
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biographie',
                'required' => false,
            ])
            ->add('color', ChoiceType::class, [
                'label' => 'Palette',
                'choices' => [
                    'Violet' => 'violet',
                    'Cyan' => 'cyan',
                    'Rose' => 'pink',
                    'Or' => 'gold',
                    'Rose pâle' => 'rose',
                    'Vert' => 'green',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
        ]);
    }
}
