<?php

namespace App\Form;

use App\Entity\Staff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class StaffFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('surname', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('aboutMe', TextareaType::class, [
                'label' => 'About Me',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 2000]),
                ],
            ])
            ->add('experience', TextareaType::class, [
                'label' => 'Experience',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 2000]),
                ],
            ])
            ->add('school', TextType::class, [
                'label' => 'School/Education',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('avatarImage', TextType::class, [
                'label' => 'Avatar Image URL',
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => 500]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Staff::class,
        ]);
    }
}
