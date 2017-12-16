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

namespace DembeloMain\Service;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class FileHandler
 * @package DembeloMain\Service
 */
class FileHandler
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * FileHandler destructor.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return $this
     */
    public function open(string $filename, string $mode): self
    {
        $fileHandler = new self();
        $fileHandler->initialize($filename, $mode);

        return $fileHandler;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        if (\is_resource($this->resource)) {
            return fclose($this->resource);
        }

        return false;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        return feof($this->resource);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public function read(int $length): string
    {
        return fread($this->resource, $length);
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return int
     */
    public function seek(int $offset, int $whence = SEEK_SET): int
    {
        return fseek($this->resource, $offset, $whence);
    }

    /**
     * @param string $string
     * @param int    $length
     *
     * @return int
     */
    public function write(string $string, int $length = null): int
    {
        if (null === $length) {
            $length = mb_strlen($string);
        }
        return fwrite($this->resource, $string, $length);
    }

    /**
     * @param string $filename
     * @param string $mode
     *
     * @return void
     *
     * @throws FileNotFoundException
     */
    private function initialize(string $filename, string $mode): void
    {
        if (!file_exists($filename)) {
            throw new FileNotFoundException(sprintf('file %s does not exist', $filename));
        }
        $this->resource = fopen($filename, $mode);
    }
}