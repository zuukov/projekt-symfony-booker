<?php

namespace App\Form;

use App\Entity\Business;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BusinessFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'] ?? false;

        $builder
            ->add('businessName', TextType::class, [
                'label' => 'Business Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
                'disabled' => $isEdit, // Make read-only when editing
            ])
            ->add('formalBusinessName', TextType::class, [
                'label' => 'Formal Business Name',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 1000]),
                ],
            ])
            ->add('address', TextType::class, [
                'label' => 'Address',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 500]),
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'City',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal Code',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 20]),
                ],
            ])
            ->add('phone', TextType::class, [
                'label' => 'Phone',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 20]),
                ],
            ])
            ->add('secondaryPhone', TextType::class, [
                'label' => 'Secondary Phone',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 20]),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('instagramUrl', UrlType::class, [
                'label' => 'Instagram URL',
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => 500]),
                ],
            ])
            ->add('facebookUrl', UrlType::class, [
                'label' => 'Facebook URL',
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => 500]),
                ],
            ])
            ->add('websiteUrl', UrlType::class, [
                'label' => 'Website URL',
                'required' => false,
                'constraints' => [
                    new Assert\Url(),
                    new Assert\Length(['max' => 500]),
                ],
            ])
            ->add('logoUrl', UrlType::class, [
                'label' => 'Logo URL',
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
            'data_class' => Business::class,
            'is_edit' => false,
        ]);

        $resolver->setAllowedTypes('is_edit', 'bool');
    }
}
