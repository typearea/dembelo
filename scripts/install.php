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
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HEADER, false);
curl_setopt($curl, CURLOPT_HTTPHEADER, array(
    'User-Agent: dembelo'
));
$data = curl_exec($curl);
curl_close($curl);

$releases = json_decode($data);

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

if (file_exists("files/version")) {
    $installedVersion = file_get_contents("files/version");
    if ($installedVersion === $newVersion) {
        echo 'latest version already installed. exit' . "\n";
        exit(1);
    }
}

echo 'download '.$downloadUrl."...\n";
shell_exec('wget -q '.$downloadUrl);
echo 'finished' . "\n";

echo 'extract '.$downloadName."...\n";
shell_exec('unzip '.$downloadName);
echo 'finished' . "\n";

echo 'clearing cache '."...\n";
shell_exec('cd www');
shell_exec('php app/console cache:clear --env=prod');
shell_exec('php app/console assetic:dump --env=prod');
echo 'finished' . "\n";

shell_exec('rm '.$downloadName);
echo "installation finished\n";
exit(0);