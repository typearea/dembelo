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

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use DembeloMain\Entity\User;

/**
 * Class DefaultController
 */
class UserController extends Controller
{
    /**
     * @Route("/login", name="login")
     *
     * @return string
     */
    public function loginAction(Request $request)
    {
        $user = new User;

        $form = $this->createFormBuilder($user)
            ->setAction($this->generateUrl('login'))
            ->add('email', 'email')
            ->add('password', 'password')
            ->add('save', 'submit', array('label' => 'Login', 'attr' => array('class' => 'btn btn-primary')))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
        }

        return $this->render(
            'user/login.html.twig',
            array(
                'dialogId' => 'modalLogin',
                'form' => $form->createView()
            )
        );
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
            array('dialogId' => 'modalRegister')
        );
    }
}