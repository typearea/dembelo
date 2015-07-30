<?php

/**
 * This file is part of the Dembelo.
 *
 * (c) Michael Giesler <michael@horsemen.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DembeloMain
 * @author Michael Giesler <michael@4horsemen.de>
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
                //'last_username' => $lastUsername,
                'error' => $error,
                'form' => $form->createView()
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