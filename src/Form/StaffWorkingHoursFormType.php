<?php

namespace App\Form;

use App\Entity\StaffWorkingHours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class StaffWorkingHoursFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weekday', ChoiceType::class, [
                'label' => 'Dzień tygodnia',
                'choices' => [
                    'Poniedziałek' => 0,
                    'Wtorek' => 1,
                    'Środa' => 2,
                    'Czwartek' => 3,
                    'Piątek' => 4,
                    'Sobota' => 5,
                    'Niedziela' => 6,
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Wybierz dzień tygodnia'),
                    new Assert\Range(min: 0, max: 6),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('startsAt', TimeType::class, [
                'label' => 'Godzina rozpoczęcia',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Podaj godzinę rozpoczęcia'),
                ],
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                ],
                'label_attr' => [
                    'class' => 'block text-sm font-medium text-gray-700 mb-1',
                ],
            ])
            ->add('endsAt', TimeType::class, [
                'label' => 'Godzina zakończenia',
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'html5' => true,
                'constraints' => [
                    new Assert\NotBlank(message: 'Podaj godzinę zakończenia'),
                    new Assert\Callback(function ($object, ExecutionContextInterface $context) {
                        $form = $context->getRoot();
                        $startsAt = $form->get('startsAt')->getData();
                        $endsAt = $form->get('endsAt')->getData();

                        if ($startsAt && $endsAt && $endsAt <= $startsAt) {
                            $context->buildViolation('Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia')
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
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StaffWorkingHours::class,
        ]);
    }
}
