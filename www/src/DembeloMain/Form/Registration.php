<?php
/* Copyright (C) 2018 Michael Giesler
 *
 * This file is part of Dembelo.
 *
 * Dembelo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Dembelo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with Dembelo. If not, see <http://www.gnu.org/licenses/>.
 */

namespace DembeloMain\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class Registration
 */
class Registration extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                ['label' => false, 'attr' => ['class' => 'u-full-width', 'placeholder' => 'Email']]
            )
            ->add(
                'password',
                PasswordType::class,
                ['label' => false, 'attr' => ['class' => 'u-full-width', 'placeholder' => 'Password']]
            )
            ->add(
                'gender',
                ChoiceType::class,
                [
                    'choices' => ['male' => 'm', 'female' => 'f'],
                    'label' => false,
                    'placeholder' => 'Gender',
                    'required' => false,
                    'attr' => ['class' => 'u-full-width'],
                ]
            )
            ->add(
                'source',
                TextType::class,
                [
                    'label' => 'Where have you first heard of Dembelo?',
                    'required' => false,
                    'attr' => ['class' => 'u-full-width'],
                ]
            )
            ->add(
                'reason',
                TextareaType::class,
                [
                    'label' => 'Why to you want to participate in our Closed Beta?',
                    'required' => false,
                    'attr' => ['class' => 'u-full-width'],
                ]
            )
            ->add(
                'save',
                SubmitType::class,
                [
                    'label' => 'Request registration',
                    'attr' => ['class' => 'button button-primary u-full-width'],
                ]
            );
    }
}
