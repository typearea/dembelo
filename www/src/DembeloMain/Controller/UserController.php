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
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

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
            )
            ->getForm();


        return $this->render(
            'DembeloMain::user/login.html.twig',
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
     * @Route("/registration", name="register")
     *
     * @param Request $request request object
     *
     * @return string
     */
    public function registrationAction(Request $request)
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setStatus(0);
        $form = $this->createFormBuilder($user)
            ->add(
                'email',
                EmailType::class,
                array('label' => false, 'attr' => array('class' => 'u-full-width', 'placeholder' => 'E-Mail'))
            )
            ->add(
                'password',
                PasswordType::class,
                array('label' => false, 'attr' => array('class' => 'u-full-width', 'placeholder' => 'Password'))
            )
            ->add(
                'gender',
                ChoiceType::class,
                array(
                    'choices' => array('male' => 'm', 'female' => 'f'),
                    'label' => false,
                    'placeholder' => 'Gender',
                    'required' => false,
                    'attr' => array('class' => 'u-full-width'),
                )
            )
            ->add(
                'source',
                TextType::class,
                array(
                    'label' => 'Where have you first heard of Dembelo?',
                    'required' => false,
                    'attr' => array('class' => 'u-full-width'),
                )
            )
            ->add(
                'reason',
                TextareaType::class,
                array(
                    'label' => 'Why to you want to participate in our Closed Beta?',
                    'required' => false,
                    'attr' => array('class' => 'u-full-width'),
                )
            )
            ->add(
                'save',
                SubmitType::class,
                array(
                    'label' => 'Request registration',
                    'attr' => array('class' => 'button button-primary u-full-width'),
                )
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mongo = $this->get('doctrine_mongodb');
            $dm = $mongo->getManager();
            $encoder = $this->get('security.password_encoder');
            $password = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setMetadata('created', time());
            $user->setMetadata('updated', time());
            $dm->persist($user);
            $dm->flush();

            return $this->redirectToRoute('registration_success');
        }

        return $this->render(
            'DembeloMain::user/register.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/registrationSuccess", name="registration_success")
     *
     * @return string
     */
    public function registrationsuccessAction()
    {
        return $this->render(
            'DembeloMain::user/registrationSuccess.html.twig'
        );
    }

    /**
     * @Route("/activation/{hash}", name="emailactivation")
     *
     * @param string $hash activation hash
     *
     * @return string
     */
    public function activateemailAction($hash)
    {
        $mongo = $this->get('doctrine_mongodb');
        $repository = $mongo->getRepository('DembeloMain:User');
        $dm = $mongo->getManager();
        $user = $repository->findOneByActivationHash($hash);
        $user->setActivationHash('');
        $user->setStatus(1);
        $dm->persist($user);
        $dm->flush();

        return $this->render(
            'DembeloMain::user/activationSuccess.html.twig'
        );
    }
}
