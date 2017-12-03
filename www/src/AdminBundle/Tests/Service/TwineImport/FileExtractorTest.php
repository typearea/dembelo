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
namespace AdminBundle\Service\TwineImport;

use PHPUnit\Framework\TestCase;

/**
 * Class FileExtractorTest
 */
class FileExtractorTest extends TestCase
{
    /**
     * @return void
     *
     * @expectedException \InvalidArgumentException
     *
     * @expectedExceptionMessage Failed to read data from file [invalidFilename].
     */
    public function testExtractForInvalidFile(): void
    {
        $filename = 'invalidFilename';
        $extractor = new FileExtractor();
        $extractor->extract($filename);
    }

    /**
     * @return void
     */
    public function testExtract(): void
    {
        $tmpName = tempnam('/tmp', 'unittest');
        $createdFilename = $tmpName.'.extracted';
        $content = <<< END
nonsens
lots of stuff
<tw-storydata attr="attr">
content
</tw-storydata>
nonsens
lots of other stuff
END;

        file_put_contents($tmpName, $content);
        $extractor = new FileExtractor();
        $extractor->extract($tmpName);
        self::assertFileExists($createdFilename);
        $fileContent = file_get_contents($createdFilename);
        self::assertContains('content', $fileContent);
        self::assertContains('tw-storydata attr="attr"', $fileContent);
        self::assertNotContains('lots of stuff', $fileContent);
        self::assertNotContains('lots of other stuff', $fileContent);
    }
}
