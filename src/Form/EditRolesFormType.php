<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditRolesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
            ->add('roles', ChoiceType::class, [
                'constraints' => [
                    new NotBlank([],"Le rôle est obligatoire")
                ],
                'choices'  => [
                    'Utilisateur' => "ROLE_ADMIN",
                    'Administrateur' => "ROLE_ADMIN",
                    
                ],
                'multiple' => true,
                'expanded' => false,
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
