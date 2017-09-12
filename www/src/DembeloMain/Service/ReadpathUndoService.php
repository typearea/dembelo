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
declare(strict_types=1);

namespace DembeloMain\Service;

use Symfony\Component\HttpFoundation\Session\Session;

class ReadpathUndoService
{
    private const SESSION_KEY = 'readpathundo';

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
        if ($session->has(self::SESSION_KEY)) {
            $this->data = $session->get(self::SESSION_KEY);
        } else {
            $this->data = [
                'past' => [],
                'present' => null,
                'future' => [],
            ];
        }

    }

    public function add(string $textnodeId): void
    {
        if ($this->data['present'] !== null) {
            $this->data['past'][] = $this->data['present'];
        }
        $this->data['present'] = $textnodeId;
        $this->data['future'] = [];
        $this->persist();
    }

    public function getCurrentItem(): ?string
    {
        return $this->data['present'];
    }

    /**
     * @throws \OutOfBoundsException
     */
    public function undo(): void
    {
        if (empty($this->data['past'])) {
            throw new \OutOfBoundsException('nothing to be undone');
        }
        $this->data['future'][] = $this->data['present'];
        $this->data['present'] = array_pop($this->data['past']);
        $this->persist();
    }

    /**
     * @throws \OutOfBoundsException
     */
    public function redo(): void
    {
        if (empty($this->data['future'])) {
            throw new \OutOfBoundsException('nothing to be redone');
        }
        $this->data['past'][] = $this->data['present'];
        $this->data['present'] = array_pop($this->data['future']);
        $this->persist();
    }

    private function persist(): void
    {
        $this->session->set(self::SESSION_KEY, $this->data);
    }
}
