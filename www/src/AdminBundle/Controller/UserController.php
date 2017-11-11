<?php
/* Copyright (C) 2017 Michael Giesler
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

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\QueryBuilder;
use DembeloMain\Model\Repository\UserRepositoryInterface;

/**
 * Class UserController
 * @Route(service="app.admin_controller_user")
 */
class UserController extends Controller
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * UserController constructor.
     * @param UserRepositoryInterface       $userRepository
     * @param \Swift_Mailer                 $mailer
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        \Swift_Mailer $mailer
    ) {
        $this->userRepository = $userRepository;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/users", name="admin_users")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function usersAction(Request $request): Response
    {
        $filters = $request->query->get('filter');

        /* @var $query QueryBuilder */
        $query = $this->userRepository->createQueryBuilder();
        if (null !== $filters) {
            foreach ($filters as $field => $value) {
                if (empty($value) && $value !== '0') {
                    continue;
                }
                if ($field === 'status') {
                    //$value = $value === 'aktiv' ? 1 : 0;
                    $query->field($field)->equals((int) $value);
                } else {
                    $query->field($field)->equals(new \MongoRegex('/.*'.$value.'.*/i'));
                }
            }
        }
        $users = $query->getQuery()->execute();

        $output = array();
        /* @var $user \DembeloMain\Document\User */
        foreach ($users as $user) {
            $obj = new \StdClass();
            $obj->id = $user->getId();
            $obj->email = $user->getEmail();
            $obj->roles = implode(', ', $user->getRoles());
            $obj->licenseeId = $user->getLicenseeId() ?? '';
            $obj->gender = $user->getGender();
            $obj->status = $user->getStatus(); // === 0 ? 'inaktiv' : 'aktiv';
            $obj->source = $user->getSource();
            $obj->reason = $user->getReason();
            $obj->created = date('Y-m-d H:i:s', $user->getMetadata()['created']);
            $obj->updated = date('Y-m-d H:i:s', $user->getMetadata()['updated']);
            $output[] = $obj;
        }

        return new Response(\json_encode($output));
    }

    /**
     * @Route("/useractivationmail", name="admin_user_activation_mail")
     *
     * @param Request $request
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function useractivationmailAction(Request $request): Response
    {
        $userId = $request->request->get('userId');

        /* @var $user \DembeloMain\Document\User */
        $user = $this->userRepository->find($userId);
        if (null === $user) {
            return new Response(\json_encode(['error' => false]));
        }
        $user->setActivationHash(sha1($user->getEmail().$user->getPassword().\time()));

        $this->userRepository->save($user);

        $message = (new \Swift_Message('waszulesen - Bestätigung der Email-Adresse'))
            ->setFrom('system@waszulesen.de')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'AdminBundle::Emails/registration.txt.twig',
                    array('hash' => $user->getActivationHash())
                ),
                'text/html'
            );

        $this->mailer->send($message);

        return new Response(\json_encode(['error' => false]));
    }
}
