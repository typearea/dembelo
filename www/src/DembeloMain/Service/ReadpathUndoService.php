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
declare(strict_types = 1);

namespace DembeloMain\Service;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class ReadpathUndoService
 */
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

    /**
     * ReadpathUndoService constructor.
     * @param Session $session
     */
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
                'undoAvoid' => null,
            ];
        }
    }

    /**
     * @param string $textnodeId
     */
    public function add(string $textnodeId): void
    {
        if ($textnodeId === $this->data['present']) {
            return;
        }
        if ($this->data['undoAvoid'] === $textnodeId) {
            $this->data['undoAvoid'] = null;
            $this->persist();

            return;
        }
        if ($this->data['present'] !== null) {
            $this->data['past'][] = $this->data['present'];
        }
        $this->data['present'] = $textnodeId;
        $this->data['future'] = [];
        $this->persist();
    }

    /**
     * @return null|string
     */
    public function getCurrentItem(): ?string
    {
        return $this->data['present'];
    }

    /**
     * @return bool
     */
    public function undo(): bool
    {
        if (empty($this->data['past'])) {
            return false;
        }
        $this->data['future'][] = $this->data['present'];
        $this->data['present'] = array_pop($this->data['past']);
        $this->data['undoAvoid'] = $this->data['present'];
        $this->persist();

        return true;
    }

    /**
     * @return bool
     */
    public function redo(): bool
    {
        if (empty($this->data['future'])) {
            return false;
        }
        $this->data['past'][] = $this->data['present'];
        $this->data['present'] = array_pop($this->data['future']);
        $this->persist();

        return true;
    }

    /**
     * @return void
     */
    private function persist(): void
    {
        $this->session->set(self::SESSION_KEY, $this->data);
    }
}
