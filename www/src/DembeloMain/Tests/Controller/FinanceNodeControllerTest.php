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

namespace DembeloMain\Tests\Controller;

use DembeloMain\Controller\FinanceNodeController;
use DembeloMain\Document\Textnode;
use DembeloMain\Model\FeatureToggle;
use DembeloMain\Model\Readpath;
use DembeloMain\Model\Repository\TextNodeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface as Templating;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class FinanceNodeControllerTest
 */
class FinanceNodeControllerTest extends TestCase
{
    /**
     * @var FinanceNodeController
     */
    private $controller;

    /**
     * @var Templating|\PHPUnit_Framework_MockObject_MockObject
     */
    private $templatingMock;

    /**
     * @var TokenStorage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tokenStorageMock;

    /**
     * @var Readpath|\PHPUnit_Framework_MockObject_MockObject
     */
    private $readpathMock;

    /**
     * @var FeatureToggle|\PHPUnit_Framework_MockObject_MockObject
     */
    private $featureToggleMock;

    /**
     * @var TextNodeRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $textnodeRepositoryMock;

    /**
     * @var AuthorizationCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $authorizationCheckerMock;

    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    private $routerMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->templatingMock = $this->createMock(Templating::class);
        $this->tokenStorageMock = $this->createMock(TokenStorage::class);
        $this->readpathMock = $this->createMock(Readpath::class);
        $this->featureToggleMock = $this->createMock(FeatureToggle::class);
        $this->textnodeRepositoryMock = $this->createMock(TextNodeRepositoryInterface::class);
        $this->authorizationCheckerMock = $this->createMock(AuthorizationCheckerInterface::class);
        $this->routerMock = $this->createMock(Router::class);

        $this->controller = new FinanceNodeController(
            $this->templatingMock,
            $this->tokenStorageMock,
            $this->readpathMock,
            $this->featureToggleMock,
            $this->textnodeRepositoryMock,
            $this->authorizationCheckerMock,
            $this->routerMock
        );
    }

    /**
     * @return void
     */
    public function testShowActionRedirectsToLoginWhenIsNeeded(): void
    {
        $this->featureToggleMock->method('hasFeature')
            ->with('login_needed')
            ->willReturn(true);
        $this->authorizationCheckerMock->method('isGranted')
            ->with('ROLE_USER')
            ->willReturn(false);
        $this->routerMock->method('generate')->willReturn('login_route');
        $arbitraryId = 'someArbitraryId';

        $returnValue = $this->controller->showAction($arbitraryId);

        self::assertInstanceOf(RedirectResponse::class, $returnValue);
    }

    /**
     * @return void
     */
    public function testShowActionRendersTemplate(): void
    {
        $this->featureToggleMock->method('hasFeature')
            ->with('login_needed')
            ->willReturn(false);
        $arbitraryId = 'someArbitraryId';

        $textnodeMock = $this->createMock(Textnode::class);

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findOneActiveByArbitraryId')
            ->with($arbitraryId)
            ->willReturn($textnodeMock);

        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturn(new Response());

        $this->controller->showAction($arbitraryId);
    }

    /**
     * @return void
     */
    public function testShowActionRedirectsToMainpageForInvalidId(): void
    {
        $this->featureToggleMock->method('hasFeature')
            ->with('login_needed')
            ->willReturn(false);
        $arbitraryId = 'someNonExistentArbitraryId';

        $this->textnodeRepositoryMock->expects(self::once())
            ->method('findOneActiveByArbitraryId')
            ->with($arbitraryId)
            ->willReturn(null);

        $this->templatingMock->expects(self::never())
            ->method('renderResponse');

        $this->routerMock->method('generate')->willReturn('maipage');

        $returnValue = $this->controller->showAction($arbitraryId);

        self::assertInstanceOf(RedirectResponse::class, $returnValue);
    }
}
