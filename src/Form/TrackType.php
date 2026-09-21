<?php

namespace App\Form;

use App\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TrackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du morceau',
                'attr' => ['placeholder' => 'Ex. Midnight Ride'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug public',
                'attr' => ['placeholder' => 'généré automatiquement'],
                'required' => false,
            ])
            ->add('duration', TextType::class, [
                'label' => 'Durée',
                'attr' => ['placeholder' => '03:42'],
            ])
            ->add('audioUrl', TextType::class, [
                'label' => 'Fichier audio',
                'attr' => ['placeholder' => 'Nom du fichier audio'],
            ])
            ->add('isPublished', CheckboxType::class, [
                'label' => 'Publier ce morceau immédiatement',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }
}
