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
 * @package AdminBundle
 */

namespace AdminBundle\Tests\Controller;

use DembeloMain\Document\Importfile;
use DembeloMain\Document\Licensee;
use DembeloMain\Document\Topic;
use DembeloMain\Document\User;
use DembeloMain\Model\Repository\Doctrine\ODM\ImportfileRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\LicenseeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TextNodeRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\TopicRepository;
use DembeloMain\Model\Repository\Doctrine\ODM\UserRepository;
use DembeloMain\Model\Repository\ImportfileRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use AdminBundle\Controller\DefaultController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;

/**
 * Class DefaultControllerTest
 */
class DefaultControllerTest extends TestCase
{
    /**
     * @var DefaultController
     */
    private $controller;

    /**
     * @var string
     */
    private $twineDirectory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    private $templatingMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserRepository
     */
    private $userRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|LicenseeRepository
     */
    private $licenseeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TopicRepository
     */
    private $topicRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ImportfileRepositoryInterface
     */
    private $importfileRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TextNodeRepository
     */
    private $textnodeRepositoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|UserPasswordEncoder
     */
    private $userPasswordEncoderMock;

    /**
     * @var string
     */
    private $topicImageDirectory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    private $filesystemMock;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->twineDirectory = '/tmp/twineDirectory';
        $this->templatingMock = $this->createTemplatingMock();
        $this->userRepositoryMock = $this->createUserRepositoryMock();
        $this->licenseeRepositoryMock = $this->createLicenseeRepositoryMock();
        $this->topicRepositoryMock = $this->createTopicRepositoryMock();
        $this->importfileRepositoryMock = $this->createImportfileRepositoryMock();
        $this->textnodeRepositoryMock = $this->createTextnodeRepositoryMock();
        $this->userPasswordEncoderMock = $this->createUserPasswordEncoderMock();
        $this->topicImageDirectory = '/tmp/topicImageDirectory';
        $this->filesystemMock = $this->createFilesystemMock();

        $this->controller = new DefaultController(
            $this->templatingMock,
            $this->userRepositoryMock,
            $this->licenseeRepositoryMock,
            $this->topicRepositoryMock,
            $this->importfileRepositoryMock,
            $this->userPasswordEncoderMock,
            $this->twineDirectory,
            $this->topicImageDirectory,
            $this->filesystemMock
        );
    }

    /**
     * tests the index action
     * @return void
     */
    public function testIndexAction(): void
    {
        $this->templatingMock->expects(self::once())
            ->method('renderResponse')
            ->willReturnCallback(function (string $template, array $arguments) {
                self::assertEquals('AdminBundle::index.html.twig', $template);
                self::assertArrayHasKey('mainMenuData', $arguments);

                return new Response();
            });
        self::assertInstanceOf(Response::class, $this->controller->indexAction());
    }

    /**
     * @return void
     */
    public function testFormsaveActionForInvalidFormType(): void
    {
        $params = [
            'formtype' => 'invalid',
            'id' => 'someId',
        ];
        $requestMock = $this->createRequestMock($params);
        $result = $this->controller->formsaveAction($requestMock);
        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":true}',
            $json
        );
    }

    /**
     * @return void
     */
    public function testFormsaveActionForMissingId(): void
    {
        $params = [
            'formtype' => 'user',
        ];
        $requestMock = $this->createRequestMock($params);
        $result = $this->controller->formsaveAction($requestMock);
        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":true}',
            $json
        );
    }

    /**
     * @return void
     */
    public function testFormsaveActionForNewUser(): void
    {
        $params = [
            'formtype' => 'user',
            'id' => 'new',
            'email' => 'someEmail',
            'password' => 'somePassword',
        ];
        $requestMock = $this->createRequestMock($params);

        $this->userRepositoryMock->expects(self::once())
            ->method('getClassName')
            ->willReturn(User::class);
        $this->userRepositoryMock->expects(self::once())
            ->method('save')
            ->willReturnCallback(function (User $user) {
                self::assertEquals('someEmail', $user->getEmail());
                self::assertEquals('somePasswordEncoded', $user->getPassword());
                $user->setId('someId');
            });
        $this->userPasswordEncoderMock->expects(self::once())
            ->method('encodePassword')
            ->with(self::anything(), 'somePassword')
            ->willReturn('somePasswordEncoded');

        $result = $this->controller->formsaveAction($requestMock);

        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":false,"newId":"someId"}',
            $json
        );
    }

    /**
     * @return void
     */
    public function testFormsaveActionForExistingTopic(): void
    {
        $params = [
            'formtype' => 'topic',
            'id' => 'someId',
            'imageFileName' => 'someImageFileName',
            'originalImageName' => 'someOriginalImageFileName',
        ];
        $requestMock = $this->createRequestMock($params);

        $topicMock = $this->createMock(Topic::class);
        $topicMock->expects(self::any())
            ->method('getId')
            ->willReturn('someId');

        $this->topicRepositoryMock->expects(self::never())
            ->method('getClassName');
        $this->topicRepositoryMock->expects(self::once())
            ->method('find')
            ->with('someId')
            ->willReturn($topicMock);
        $this->topicRepositoryMock->expects(self::exactly(2))
            ->method('save')
            ->with($topicMock);
        $this->userPasswordEncoderMock->expects(self::never())
            ->method('encodePassword');

        $targetFileName = $this->topicImageDirectory.'someId/someOriginalImageFileName';
        $this->filesystemMock->expects(self::once())
            ->method('rename')
            ->with(self::anything(), $targetFileName);

        $result = $this->controller->formsaveAction($requestMock);

        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":false,"newId":"someId"}',
            $json
        );
    }

    /**
     * @return void
     */
    public function testFormsaveActionForExistingLicensee(): void
    {
        $params = [
            'formtype' => 'licensee',
            'id' => 'someId',
        ];
        $requestMock = $this->createRequestMock($params);

        $licenseeMock = $this->createMock(Licensee::class);
        $licenseeMock->expects(self::any())
            ->method('getId')
            ->willReturn('someId');

        $this->licenseeRepositoryMock->expects(self::never())
            ->method('getClassName');
        $this->licenseeRepositoryMock->expects(self::once())
            ->method('find')
            ->with('someId')
            ->willReturn($licenseeMock);
        $this->licenseeRepositoryMock->expects(self::once())
            ->method('save')
            ->with($licenseeMock);

        $result = $this->controller->formsaveAction($requestMock);

        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":false,"newId":"someId"}',
            $json
        );
    }

    /**
     * @return void
     */
    public function testFormsaveActionForExistingImportfile(): void
    {
        $params = [
            'formtype' => 'importfile',
            'id' => 'someId',
            'filename' => 'someFilename',
            'orgname' => 'someOrgName',
        ];
        $requestMock = $this->createRequestMock($params);

        $importfileMock = $this->createMock(Importfile::class);
        $importfileMock->expects(self::any())
            ->method('getId')
            ->willReturn('someId');

        $this->importfileRepositoryMock->expects(self::never())
            ->method('getClassName');
        $this->importfileRepositoryMock->expects(self::once())
            ->method('find')
            ->with('someId')
            ->willReturn($importfileMock);
        $this->importfileRepositoryMock->expects(self::exactly(2))
            ->method('save')
            ->with($importfileMock);

        $this->filesystemMock->expects(self::once())
            ->method('exists')
            ->willReturn(true);

        $result = $this->controller->formsaveAction($requestMock);

        self::assertInstanceOf(Response::class, $result);
        $json = $result->getContent();
        self::assertJsonStringEqualsJsonString(
            '{"error":false,"newId":"someId"}',
            $json
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LicenseeRepository
     */
    private function createLicenseeRepositoryMock(): LicenseeRepository
    {
        return $this->createMock(LicenseeRepository::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImportfileRepository
     */
    private function createImportfileRepositoryMock(): ImportfileRepository
    {
        return $this->createMock(ImportfileRepository::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EngineInterface
     */
    private function createTemplatingMock(): EngineInterface
    {
        return $this->createMock(EngineInterface::class);
    }

    /**
     * @return UserRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserRepositoryMock(): UserRepository
    {
        return $this->createMock(UserRepository::class);
    }

    /**
     * @return TopicRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTopicRepositoryMock(): TopicRepository
    {
        return $this->createMock(TopicRepository::class);
    }

    /**
     * @return TextNodeRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createTextnodeRepositoryMock(): TextNodeRepository
    {
        return $this->createMock(TextNodeRepository::class);
    }

    /**
     * @return UserPasswordEncoder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createUserPasswordEncoderMock(): UserPasswordEncoder
    {
        return $this->createMock(UserPasswordEncoder::class);
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function createRequestMock($params): Request
    {
        $parameterBagMock = $this->createMock(ParameterBag::class);
        $parameterBagMock->expects(self::any())
            ->method('all')
            ->willReturn($params);

        /**
         * @var $mock \PHPUnit_Framework_MockObject_MockObject|Request
         */
        $mock = $this->createMock(Request::class);
        $mock->request = $parameterBagMock;

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Filesystem
     */
    private function createFilesystemMock(): Filesystem
    {
        return $this->createMock(Filesystem::class);
    }
}
