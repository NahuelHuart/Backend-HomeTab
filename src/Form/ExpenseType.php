<?php

namespace App\Form;

use App\Entity\Expense;
use App\Entity\Household;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpenseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('amount')
            ->add('category')
            ->add('paidAt')
            ->add('isPaid')
            ->add('notes')
            ->add('paidBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
            ->add('household', EntityType::class, [
                'class' => Household::class,
                'choice_label' => 'id',
            ])
            ->add('splitBetween', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Expense::class,
        ]);
    }
}
