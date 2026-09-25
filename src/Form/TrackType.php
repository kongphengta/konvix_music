<?php

namespace App\Form;

use App\Entity\Track;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
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
            ->add('musicStyle', TextType::class, [
                'label' => 'Style de musique',
                'required' => false,
                'attr' => ['placeholder' => 'Ex. Soul, R&B, House'],
            ])
            ->add('language', TextType::class, [
                'label' => 'Langue de la musique',
                'required' => false,
                'attr' => ['placeholder' => 'Ex. Français, Anglais'],
            ])
            ->add('audioUrl', TextType::class, [
                'label' => 'Lien audio (optionnel)',
                'required' => false,
                'attr' => ['placeholder' => 'https://...'],
            ])
            ->add('audioFile', FileType::class, [
                'label' => 'Fichier audio',
                'mapped' => false,
                'required' => false,
            ])
            ->add('coverImage', FileType::class, [
                'label' => 'Pochette du morceau',
                'mapped' => false,
                'required' => false,
            ])
            ->add('publishMode', ChoiceType::class, [
                'label' => 'Publication',
                'mapped' => false,
                'choices' => [
                    'Publier ce morceau immédiatement' => 'immediate',
                    'Publier programmé' => 'scheduled',
                ],
                'expanded' => true,
                'multiple' => false,
                'data' => $options['data'] && $options['data']->getPublishedAt() && !$options['data']->isPublished() ? 'scheduled' : 'immediate',
            ])
            ->add('publishedAt', DateType::class, [
                'label' => 'Date de publication',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'data' => $options['data'] && $options['data']->getPublishedAt() ? $options['data']->getPublishedAt() : new \DateTimeImmutable('+1 day'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Track::class,
        ]);
    }
}
