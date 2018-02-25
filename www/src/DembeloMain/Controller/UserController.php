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
use DembeloMain\Form\Login;
use DembeloMain\Form\Registration;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

/**
 * Class DefaultController
 * @Route(service="app.controller_user")
 */
class UserController extends Controller
{
    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @var Templating
     */
    private $templating;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoder;

    /**
     * UserController constructor.
     * @param AuthenticationUtils $authenticationUtils
     * @param UserRepositoryInterface $userRepository
     * @param DocumentManager $documentManager
     * @param Templating $templating
     */
    public function __construct(AuthenticationUtils $authenticationUtils, UserRepositoryInterface $userRepository, DocumentManager $documentManager, Templating $templating, Router $router, FormFactoryInterface $formFactory, UserPasswordEncoder $passwordEncoder)
    {
        $this->authenticationUtils = $authenticationUtils;
        $this->userRepository = $userRepository;
        $this->documentManager = $documentManager;
        $this->templating = $templating;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/login", name="login_route")
     *
     * @return Response
     */
    public function loginAction(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $user = new User();
        $user->setEmail($lastUsername);

        $url = $this->router->generate('login_check');

        $form = $this->formFactory->create(Login::class, $user, [
            'action' => $url,
        ]);

        return $this->templating->renderResponse(
            'DembeloMain::user/login.html.twig',
            [
                'error' => $error,
                'form' => $form,
            ]
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     *
     * @return void
     */
    public function loginCheckAction(): void
    {
    }

    /**
     * @Route("/registration", name="register")
     *
     * @param Request $request request object
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function registrationAction(Request $request): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_USER']);
        $user->setStatus(0);

        $form = $this->formFactory->create(Registration::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $user->setMetadata('created', time());
            $user->setMetadata('updated', time());
            $this->documentManager->persist($user);
            $this->documentManager->flush();

            return $this->redirectToRoute('registration_success');
        }

        return $this->templating->renderResponse(
            'DembeloMain::user/register.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/registrationSuccess", name="registration_success")
     *
     * @return Response
     */
    public function registrationsuccessAction(): Response
    {
        return $this->templating->renderResponse('DembeloMain::user/registrationSuccess.html.twig');
    }

    /**
     * @Route("/activation/{hash}", name="emailactivation")
     *
     * @param string $hash activation hash
     *
     * @return Response
     */
    public function activateemailAction(string $hash): Response
    {
        $user = $this->userRepository->findOneBy(['activationHash' => $hash]);
        if (null === $user) {
            throw new \InvalidArgumentException('no user found for hash');
        }
        $user->setActivationHash('');
        $user->setStatus(1);
        $this->documentManager->persist($user);
        $this->documentManager->flush();

        return $this->templating->renderResponse('DembeloMain::user/activationSuccess.html.twig');
    }

    /**
     * @param string $route
     * @param array  $parameters
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, array $parameters = array(), $status = 302): RedirectResponse
    {
        $url = $this->router->generate($route, $parameters);

        return new RedirectResponse($url, $status);
    }
}
