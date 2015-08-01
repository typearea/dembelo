<?php

/* Copyright (C) 2015 Michael Giesler
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


/**
 * @package DembeloMain
 */

namespace DembeloMain\Controller;

use DembeloMain\Document\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DefaultController
 */
class UserController extends Controller
{
    /**
     * @Route("/login", name="login_route")
     *
     * @return string
     */
    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = new User();
        $user->setEmail($lastUsername);

        $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('login_check'))
            ->add('_username', 'email', array('label' => 'Email'))
            ->add('password', 'password', array('label' => 'Passwort'))
            ->add('save', 'submit', array('label' => 'Einloggen', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();


        return $this->render(
            'user/login.html.twig',
            array(
                'error' => $error,
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     *
     * @return string
     */
    public function loginCheckAction()
    {

    }

    /**
     * @Route("/register", name="register")
     *
     * @return string
     */
    public function registerAction()
    {
        return $this->render(
            'user/register.html.twig',
            array()
        );
    }
}
