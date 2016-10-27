<?php
/* Copyright (C) 2016 Michael Giesler
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

$url = 'https://api.github.com/repos/typearea/dembelo/releases';
$data = shell_exec('curl '.$url);

$releases = json_decode($data);

if (is_null($releases)) {
    echo 'Data can\'t be parsed as JSON. exit' . "\n";
    exit(1);
}

$latestReleaseDate = 0;
$downloadUrl = '';

foreach ($releases as $release) {
    if (count($release->assets) > 0) {
        if ($latestReleaseDate < $release->published_at) {
            $latestReleaseDate = $release->published_at;
            $downloadUrl = $release->assets[0]->browser_download_url;
            $downloadName = $release->assets[0]->name;
            $newVersion = $release->tag_name;
        }
    }
}

if ($latestReleaseDate === 0) {
    echo 'No release found. exit' . "\n";
    exit(1);
}

if (file_exists("files/version")) {
    $installedVersion = file_get_contents("files/version");
    if ($installedVersion === $newVersion) {
        echo 'latest version ['.$installedVersion.'] already installed. exit' . "\n";
        exit(1);
    }
}

echo 'download '.$downloadUrl."...\n";
shell_exec('wget -q '.$downloadUrl);
echo 'finished' . "\n";

echo 'extract '.$downloadName."...\n";
shell_exec('unzip -o '.$downloadName);
echo 'finished' . "\n";

echo 'clearing cache '."...\n";
shell_exec('cd www');
shell_exec('php app/console cache:clear --env=prod');
shell_exec('php app/console assetic:dump --env=prod');
echo 'finished' . "\n";

shell_exec('rm '.$downloadName);
echo 'installation ['.$newVersion.'] finished'."\n";
exit(0);