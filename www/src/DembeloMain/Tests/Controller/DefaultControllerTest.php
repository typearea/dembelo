<?php

declare(strict_types=1);

/* Copyright (C) 2015 Michael Giesler, Stephan Kreutzer
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

use DembeloMain\Model\FavoriteManager;
use DembeloMain\Model\FeatureToggle;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * @var DefaultController
     */
    private $controller;

    /**
     * @var FeatureToggle|\PHPUnit_Framework_MockObject_MockObject
     */
    private $featureToggleMock;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationCheckerMock;

    /**
     * @var UserRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $userRepositoryMock;

    /**
     * @var TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $textnodeRepositoryMock;

    /**
     * @var Templating|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templatingMock;

    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routerMock;

    /**
     * @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorageMock;

    /**
     * @var ReadPath|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readpathMock;

    /**
     * @var FavoriteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $favoriteManagerMock;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->featureToggleMock = $this->createFeatureToggleMock();
        $this->authorizationCheckerMock = $this->createAuthorizationCheckerMock();
        $this->userRepositoryMock = $this->createUserRepositoryMock();
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->templatingMock = $this->createTemplatingMock();
        $this->routerMock = $this->createRouterMock();
        $this->tokenStorageMock = $this->createTokenStorageMock();
        $this->readpathMock = $this->createReadpathMock();
        $this->favoriteManagerMock = $this->createFavoriteManagerMock();

        $this->controller = new DefaultController(
            $this->featureToggleMock,
            $this->authorizationCheckerMock,
            $this->userRepositoryMock,
            $this->textnodeRepositoryMock,
            $this->templatingMock,
            $this->routerMock,
            $this->tokenStorageMock,
            $this->readpathMock,
            $this->favoriteManagerMock
        );
    }

    /**
     * Tests the index action.
     */
    public function testReadTopicActionWithLoggedOutUserAndEnabledLoginFeature(): void
    {
        $topicId = 'someTopicId';

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(true);
        $this->authorizationCheckerMock->expects(self::any())
            ->method('isGranted')
            ->willReturn(false);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('login_route', []);

        $result = $this->controller->readTopicAction($topicId);
        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * Tests the index action.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testReadTopicActionForInvalidTopicId(): void
    {
        $topicId = 'someTopicId';

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('getTextnodeToRead')
            ->with($topicId)
            ->willReturn(null);

        $this->controller->readTopicAction($topicId);
    }

    /**
     * @return FeatureToggle|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFeatureToggleMock(): FeatureToggle
    {
        return $this->createMock(FeatureToggle::class);
    }

    /**
     * @return AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createAuthorizationCheckerMock(): AuthorizationCheckerInterface
    {
        return $this->createMock(AuthorizationCheckerInterface::class);
    }

    /**
     * @return UserRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserRepositoryMock(): UserRepositoryInterface
    {
        return $this->createMock(UserRepositoryInterface::class);
    }

    /**
     * @return TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTextnodeRepositoryMock(): TextNodeRepositoryInterface
    {
        return $this->createMock(TextNodeRepositoryInterface::class);
    }

    /**
     * @return Templating|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTemplatingMock(): Templating
    {
        return $this->createMock(Templating::class);
    }

    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createRouterMock(): Router
    {
        $mock = $this->createMock(Router::class);
        $mock->expects(self::any())
            ->method('generate')
            ->willReturn('someUrl');
        return $mock;
    }

    /**
     * @return TokenStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTokenStorageMock(): TokenStorage
    {
        return $this->createMock(TokenStorage::class);
    }

    /**
     * @return ReadPath|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createReadpathMock(): Readpath
    {
        return $this->createMock(Readpath::class);
    }

    /**
     * @return FavoriteManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createFavoriteManagerMock(): FavoriteManager
    {
        return $this->createMock(FavoriteManager::class);
    }
}
