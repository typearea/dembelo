<?php

declare(strict_types = 1);

/* Copyright (C) 2015-2017 Michael Giesler, Stephan Kreutzer
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

use DembeloMain\Document\Textnode;
use DembeloMain\Document\TextnodeHitch;
use DembeloMain\Document\User;
use DembeloMain\Model\FavoriteManager;
use DembeloMain\Model\FeatureToggle;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use DembeloMain\Model\Repository\UserRepositoryInterface;
use DembeloMain\Service\ReadpathUndoService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use DembeloMain\Controller\DefaultController;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
     * @var ReadpathUndoService|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readpathUndoServiceMock;

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
        $this->readpathUndoServiceMock = $this->createReadpathUndoServiceMock();

        $this->controller = new DefaultController(
            $this->featureToggleMock,
            $this->authorizationCheckerMock,
            $this->userRepositoryMock,
            $this->textnodeRepositoryMock,
            $this->templatingMock,
            $this->routerMock,
            $this->tokenStorageMock,
            $this->readpathMock,
            $this->favoriteManagerMock,
            $this->readpathUndoServiceMock
        );
    }

    /**
     * tests readTopicAction with guest user and enabled login feature
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
     * tests readTOpicAction for invalid topic id
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
     * test readtopicAction for valid topic and guest user
     */
    public function testReadTopicActionForValidTopicIdAndGuestUser(): void
    {
        $topicId = 'someTopicId';

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('1');
        $hitchMock->method('getDescription')->willReturn(2);
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');
        $textnode->appendHitch($hitchMock);

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('getTextnodeToRead')
            ->with($topicId)
            ->willReturn($textnode);
        $this->tokenStorageMock->expects(self::once())
            ->method('getToken')
            ->willReturn(null);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('text', ['textnodeArbitraryId' => 'someArbitraryId']);

        $result = $this->controller->readTopicAction($topicId);
        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * tests readTopicACtion for valid topic id and guest user for a finance node
     */
    public function testReadTopicActionForValidTopicIdAndGuestUserForAFinanceNode(): void
    {
        $topicId = 'someTopicId';

        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('getTextnodeToRead')
            ->with($topicId)
            ->willReturn($textnode);
        $this->tokenStorageMock->expects(self::once())
            ->method('getToken')
            ->willReturn(null);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('financenode', ['textnodeArbitraryId' => 'someArbitraryId']);

        $result = $this->controller->readTopicAction($topicId);
        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * tests readTopicAction for valid topic Id and logged in user
     */
    public function testReadTopicActionForValidTopicIdAndLoggedInUser(): void
    {
        $topicId = 'someTopicId';

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('1');
        $hitchMock->method('getDescription')->willReturn(2);
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');
        $textnode->appendHitch($hitchMock);

        $user = new User();

        $this->userRepositoryMock->expects(self::once())
            ->method('save')
            ->with($user);

        $tokenMock = $this->createTokenMock();
        $tokenMock->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('getTextnodeToRead')
            ->with($topicId)
            ->willReturn($textnode);
        $this->tokenStorageMock->expects(self::once())
            ->method('getToken')
            ->willReturn($tokenMock);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('text', ['textnodeArbitraryId' => 'someArbitraryId']);

        $result = $this->controller->readTopicAction($topicId);
        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertSame($topicId, $user->getLastTopicId());
    }

    /**
     * tests imprint action
     */
    public function testImprintAction(): void
    {
        $responseMock = $this->createMock(Response::class);
        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->with('DembeloMain::default/imprint.html.twig')
            ->willReturn($responseMock);
        $result = $this->controller->imprintAction();
        self::assertSame($responseMock, $result);
    }

    /**
     * tests back action
     */
    public function testBackActionSuccess(): void
    {
        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');

        $this->readpathUndoServiceMock->expects(self::once())
            ->method('undo')
            ->willReturn(true);
        $this->readpathUndoServiceMock->expects(self::once())
            ->method('getCurrentItem')
            ->willReturn(5);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('find')
            ->with(5)
            ->willReturn($textnode);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('text', ['textnodeArbitraryId' => 'someArbitraryId'])
            ->willReturn('someUrl');

        $result = $this->controller->backAction();
        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertSame('someUrl', $result->getTargetUrl());
    }

    /**
     * tests back action when undo() ist not successful
     */
    public function testBackActionUnsuccessfulForGuestUser(): void
    {
        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');

        $this->readpathUndoServiceMock->expects(self::once())
            ->method('undo')
            ->willReturn(false);
        $this->readpathUndoServiceMock->expects(self::never())
            ->method('getCurrentItem');
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('mainpage')
            ->willReturn('someUrl');

        $result = $this->controller->backAction();
        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertSame('someUrl', $result->getTargetUrl());
    }

    /**
     * tests back action when undo() ist not successful
     */
    public function testBackActionUnsuccessfulForLoggedInUser(): void
    {
        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbitraryId');

        $user = new User();
        $user->setLastTopicId('someLastTopicId');

        $tokenMock = $this->createTokenMock();
        $tokenMock->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorageMock->expects(self::once())
            ->method('getToken')
            ->willReturn($tokenMock);
        $this->readpathUndoServiceMock->expects(self::once())
            ->method('undo')
            ->willReturn(false);
        $this->readpathUndoServiceMock->expects(self::never())
            ->method('getCurrentItem');
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('themenfeld', ['topicId' => 'someLastTopicId'])
            ->willReturn('someUrl');

        $result = $this->controller->backAction();
        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertSame('someUrl', $result->getTargetUrl());
    }
    /**
     * tests readTextnodeAction with guest user and enabled login feature
     */
    public function testReadTextnodeActionWithGuestUserAndEnabledLoginFeature(): void
    {
        $textnodeArbitraryId = 'someTopicId';

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(true);
        $this->authorizationCheckerMock->expects(self::any())
            ->method('isGranted')
            ->willReturn(false);
        $this->routerMock->expects(self::once())
            ->method('generate')
            ->with('login_route', []);

        $result = $this->controller->readTextnodeAction($textnodeArbitraryId);
        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * tests readTextnodeAction for invalid Id
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testReadTextnodeActionForInvalidId(): void
    {
        $textnodeArbitraryId = 'someId';

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findOneActiveByArbitraryId')
            ->with($textnodeArbitraryId)
            ->willReturn(null);

        $this->controller->readTextnodeAction($textnodeArbitraryId);
    }

    /**
     * test readTextnodeAction for valid textnode
     */
    public function testReadTextnodeActionForValidTextnode(): void
    {
        $textnodeArbitraryId = 'someArbitraryId';
        $textnodeId = 'someId';

        $hitchMock = $this->createMock(TextnodeHitch::class);
        $hitchMock->method('getTextnodeId')->willReturn('1');
        $hitchMock->method('getDescription')->willReturn(2);
        $hitchMock->method('getStatus')->willReturn(Textnode::HITCH_STATUS_ACTIVE);

        $textnode = new Textnode();
        $textnode->setId($textnodeId);
        $textnode->setArbitraryId($textnodeArbitraryId);
        $textnode->appendHitch($hitchMock);

        $responseMock = $this->createMock(Response::class);

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findOneActiveByArbitraryId')
            ->with($textnodeArbitraryId)
            ->willReturn($textnode);
        $this->tokenStorageMock->expects(self::once())
            ->method('getToken')
            ->willReturn(null);
        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->with(
                'DembeloMain::default/read.html.twig',
                [
                    'textnode' => $textnode,
                    'hitches' => [],
                ]
            )
            ->willReturn($responseMock);
        $this->readpathUndoServiceMock->expects(self::once())
            ->method('add')
            ->with($textnodeId);

        $returnValue = $this->controller->readTextnodeAction($textnodeArbitraryId);
        self::assertSame($responseMock, $returnValue);
    }

    /**
     * tests readTextnodeAction for finance node
     */
    public function testReadTextnodeActionForFinanceNode(): void
    {
        $textnodeArbitraryId = 'someId';

        $textnode = new Textnode();
        $textnode->setArbitraryId('someArbId');

        $this->featureToggleMock->expects(self::once())
            ->method('hasFeature')
            ->willReturn(false);
        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findOneActiveByArbitraryId')
            ->with($textnodeArbitraryId)
            ->willReturn($textnode);

        $result = $this->controller->readTextnodeAction($textnodeArbitraryId);
        self::assertInstanceOf(RedirectResponse::class, $result);
    }

    /**
     * @return TokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTokenMock(): TokenInterface
    {
        return $this->createMock(TokenInterface::class);
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

    /**
     * @return ReadpathUndoService|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createReadpathUndoServiceMock(): ReadpathUndoService
    {
        return $this->createMock(ReadpathUndoService::class);
    }
}
