<?php

namespace App\Form;

use App\Entity\Service;
use App\Entity\ServiceCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nazwa usługi',
                'constraints' => [
                    new Assert\NotBlank(message: 'Nazwa usługi jest wymagana'),
                    new Assert\Length(max: 255, maxMessage: 'Nazwa nie może być dłuższa niż 255 znaków'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 1000, maxMessage: 'Opis nie może być dłuższy niż 1000 znaków'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'rows' => 4,
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('category', EntityType::class, [
                'label' => 'Kategoria',
                'class' => ServiceCategory::class,
                'choice_label' => 'categoryFullName',
                'placeholder' => 'Wybierz kategorię',
                'constraints' => [
                    new Assert\NotBlank(message: 'Kategoria jest wymagana'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('durationMinutes', IntegerType::class, [
                'label' => 'Czas trwania (minuty)',
                'constraints' => [
                    new Assert\NotBlank(message: 'Czas trwania jest wymagany'),
                    new Assert\Positive(message: 'Czas trwania musi być większy niż 0'),
                    new Assert\LessThanOrEqual(value: 480, message: 'Czas trwania nie może przekraczać 480 minut (8 godzin)'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'min' => 1,
                    'max' => 480,
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('price', NumberType::class, [
                'label' => 'Cena (PLN)',
                'constraints' => [
                    new Assert\NotBlank(message: 'Cena jest wymagana'),
                    new Assert\PositiveOrZero(message: 'Cena nie może być ujemna'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'min' => 0,
                    'step' => 0.01,
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('featuredImage', UrlType::class, [
                'label' => 'URL zdjęcia',
                'required' => false,
                'constraints' => [
                    new Assert\Url(message: 'Podaj prawidłowy URL'),
                    new Assert\Length(max: 500, maxMessage: 'URL nie może być dłuższy niż 500 znaków'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Usługa aktywna',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Service::class,
        ]);
    }
}
