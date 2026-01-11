<?php

namespace App\Form;

use App\Entity\BusinessWorkingHours;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BusinessWorkingHoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('weekday', HiddenType::class)
            ->add('opensAt', TimeType::class, [
                'label' => false,
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'placeholder' => '--:--'
                ],
            ])
            ->add('closesAt', TimeType::class, [
                'label' => false,
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'html5' => true,
                'attr' => [
                    'class' => 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500',
                    'placeholder' => '--:--'
                ],
            ]);

        // Add validation using form events instead of constraints
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof BusinessWorkingHours) {
                $opensAt = $data->getOpensAt();
                $closesAt = $data->getClosesAt();

                // Only validate if both times are set
                if ($opensAt && $closesAt && $closesAt <= $opensAt) {
                    $form->get('closesAt')->addError(
                        new FormError('Godzina zamknięcia musi być późniejsza niż godzina otwarcia')
                    );
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BusinessWorkingHours::class,
        ]);
    }
}
