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
namespace DembeloMain\Tests\Controller;

use DembeloMain\Document\User;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use PHPUnit\Framework\TestCase;
use DembeloMain\Controller\UserController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;

/**
 * Class DefaultControllerTest
 */
class UserControllerTest extends TestCase
{
    /**
     * @var UserController
     */
    private $controller;

    /**
     * @var AuthenticationUtils|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authenticationUtilsMock;

    /**
     * @var UserRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    /**
     * @var DocumentManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $documentManagerMock;

    /**
     * @var Templating|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templatingMock;

    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routerMock;

    /**
     * @var FormFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formFactoryMock;

    /**
     * @var UserPasswordEncoder
     */
    private $passwordEncoderMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticationUtilsMock = $this->createMock(AuthenticationUtils::class);
        $this->userRepositoryMock = $this->createMock(UserRepositoryInterface::class);
        $this->documentManagerMock = $this->createMock(DocumentManager::class);
        $this->templatingMock = $this->createMock(Templating::class);
        $this->routerMock = $this->createMock(Router::class);
        $this->formFactoryMock = $this->createMock(FormFactoryInterface::class);
        $this->passwordEncoderMock = $this->createMock(UserPasswordEncoder::class);

        $this->controller = new UserController(
            $this->authenticationUtilsMock,
            $this->userRepositoryMock,
            $this->documentManagerMock,
            $this->templatingMock,
            $this->routerMock,
            $this->formFactoryMock,
            $this->passwordEncoderMock
        );
    }

    /**
     * @return void
     */
    public function testLoginAction(): void
    {
        $loginFormMock = $this->createMock(FormInterface::class);
        $this->formFactoryMock->method('create')->willReturn($loginFormMock);

        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturn($responseMock);

        $this->controller->loginAction();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testRegistrationActionNotSubmitted(): void
    {
        $registrationFormMock = $this->createMock(FormInterface::class);
        $this->formFactoryMock->method('create')->willReturn($registrationFormMock);
        $registrationFormMock->method('isSubmitted')->willReturn(false);

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturn($responseMock);

        $this->documentManagerMock->expects(self::never())->method('persist');
        $this->documentManagerMock->expects(self::never())->method('flush');

        $this->controller->registrationAction($request);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function testRegistrationActionSubmittedAndValid(): void
    {
        $registrationFormMock = $this->createMock(FormInterface::class);
        $this->formFactoryMock->method('create')->willReturn($registrationFormMock);
        $registrationFormMock->method('isSubmitted')->willReturn(true);
        $registrationFormMock->method('isValid')->willReturn(true);

        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(Request::class);

        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::never())->method('renderResponse');

        $this->routerMock->method('generate')->willReturn('registration_success');

        $this->documentManagerMock->expects(self::once())->method('persist');
        $this->documentManagerMock->expects(self::once())->method('flush');

        $this->controller->registrationAction($request);
    }

    /**
     * @return void
     */
    public function testRegistrationsuccessAction(): void
    {
        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturn($responseMock);
        $this->controller->registrationsuccessAction();
    }

    /**
     * @return void
     */
    public function testActivateemailActionUserNotFound(): void
    {
        $hash = 'someHash';
        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::never())->method('renderResponse');

        $this->expectException(\InvalidArgumentException::class);
        $this->controller->activateemailAction($hash);
    }

    /**
     * @return void
     */
    public function testActivateemailActionUserFound(): void
    {
        $hash = 'someHash';
        $responseMock = $this->createMock(Response::class);

        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturn($responseMock);

        $userMock = $this->createMock(User::class);

        $this->userRepositoryMock->method('findOneBy')
            ->willReturn($userMock);

        $this->controller->activateemailAction($hash);
    }
}
