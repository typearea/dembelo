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
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class Login
 */
class Login extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                '_username',
                EmailType::class,
                array('label' => false, 'attr' => array('class' => 'u-full-width', 'placeholder' => 'Email'))
            )
            ->add(
                'password',
                PasswordType::class,
                array('label' => false, 'attr' => array('class' => 'u-full-width', 'placeholder' => 'Password'))
            )
            ->add(
                'save',
                SubmitType::class,
                array('label' => 'Login', 'attr' => array('class' => 'button button-primary u-full-width'))
            );
    }
}