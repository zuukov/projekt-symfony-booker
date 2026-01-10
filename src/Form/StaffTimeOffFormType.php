<?php

namespace App\Form;

use App\Entity\StaffTimeOff;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StaffTimeOffFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startsAt', DateTimeType::class, [
                'label' => 'Data i godzina rozpoczęcia',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Podaj datę i godzinę rozpoczęcia'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('endsAt', DateTimeType::class, [
                'label' => 'Data i godzina zakończenia',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Podaj datę i godzinę zakończenia'),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $startsAt = $form->get('startsAt')->getData();
                        $endsAt = $form->get('endsAt')->getData();

                        if ($startsAt && $endsAt && $endsAt <= $startsAt) {
                            $context->buildViolation('Data zakończenia musi być późniejsza niż data rozpoczęcia')
                                ->addViolation();
                        }
                    }),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('reason', TextareaType::class, [
                'label' => 'Powód (opcjonalnie)',
                'required' => false,
                'constraints' => [
                    new Assert\Length(max: 1000),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'rows' => 3,
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StaffTimeOff::class,
        ]);
    }
}
