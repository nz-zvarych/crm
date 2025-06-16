<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;

class ProductForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('thumbnail', FileType::class, [
                'data_class' => null,
                'constraints' => [
                    new File(
                        maxSize: '1024k',
                        extensions: ['png', 'jpg', 'jpeg'],
                        extensionsMessage: 'Please upload a valid PDF document',
                    )
                ],
            ])
            ->add('title', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Title cannot be blank',
                    ]),
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Title cannot be longer than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('description')
            ->add('price', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Price cannot be blank',
                    ]),
                ],
            ])
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    'US Dollar (USD)' => 'USD',
                    'Euro (EUR)' => 'EUR',
                    'British Pound (GBP)' => 'GBP',
                    'Japanese Yen (JPY)' => 'JPY',
                    'Canadian Dollar (CAD)' => 'CAD',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Currency cannot be blank',
                    ]),
                ],
            ])
            ->add('amount', NumberType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Amount cannot be blank',
                    ]),
                ],
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
            ])
            ->add('author', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
