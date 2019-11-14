<?php

namespace App\Form;

use App\Entity\Partner;
use App\Entity\User;
use App\Repository\PartnerRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserCreationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',EmailType::class,[
                'attr'=> [
                    'class'=> 'form-control shadow-none m-0',
                    'placeholder' => 'Email...'
                ]
            ])
            ->add('firstName',TextType::class,[
                'attr'=> [
                    'class'=> 'form-control shadow-none m-0',
                    'placeholder' => 'First name...'
                ]
            ])
            ->add('lastName',TextType::class,[
                'attr'=> [
                    'class'=> 'form-control shadow-none m-0',
                    'placeholder' => 'Last name...'
                ]
            ])
            ->add('password',PasswordType::class,[
                'attr'=> [
                    'class'=> 'form-control shadow-none m-0',
                    'placeholder' => 'Password...'
                ]
            ])
//            ->add('authToken',)
            ->add('partner',EntityType::class,[
                'class'=>Partner::class,
                'choice_label' => 'name',
                'attr'=> [
                    'class'=> 'form-control shadow-none m-0'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
