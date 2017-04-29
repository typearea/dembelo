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

$shortOptions = 'u:';

$longOptions = [
    'help',
    'branch:'
];

$options = getopt($shortOptions, $longOptions);

if (array_key_exists('help', $options)) {
    if (file_exists('./files/version')) {
        echo "dembelo install script, " . file_get_contents('./files/version') . "\n";
    } else {
        echo "dembelo install script, unknown version\n";
    }
    echo "\n";
    echo "options:\n";
    echo " -u\tuser for chmod (for example: www-data)\n";
    echo "--branch\tinstall a git branch instead of release\n";
    echo "--help\tdisplays this help\n";
    exit(0);
}

if (array_key_exists('branch', $options)) {
    installBranch($options['branch']);
} else {
    $downloadName = installLatestRelease();
}

if (array_key_exists('u', $options)) {
    echo 'chown to '.$options['u']."...\n";
    shell_exec('chown -R '.$options['u']. ' www/');
}

echo 'clearing cache '."...\n";
shell_exec('cd www && php bin/console cache:clear --env=prod');
shell_exec('cd www && php bin/console assetic:dump --env=prod');
echo 'finished'."\n";

echo 'prepare some apache stuff'."\n";
shell_exec('cp files/apache/htaccess www/web/.htaccess');
if (array_key_exists('u', $options)) {
    shell_exec('chown ' . $options['u'] . ' www/web/.htaccess');
}
echo 'finished'."\n";

//shell_exec('rm ' . $downloadName);
echo 'installation finished'."\n";
exit(0);

/**
 * some functions
 */
function installLatestRelease()
{
    $url = 'https://api.github.com/repos/typearea/dembelo/releases';
    $data = shell_exec('curl ' . $url);

    $releases = json_decode($data);

    if (is_null($releases)) {
        echo 'Data can\'t be parsed as JSON. exit' . "\n";
        exit(1);
    }

    $latestReleaseDate = 0;
    $downloadUrl = '';
    $downloadName = '';
    $newVersion = '';

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
        $installedVersion = trim(file_get_contents("files/version"));
        if ($installedVersion === $newVersion) {
            echo 'latest version [' . $installedVersion . '] already installed. exit' . "\n";
            exit(1);
        }
    }

    echo 'download ' . $downloadUrl . "...\n";
    shell_exec('wget -q ' . $downloadUrl);
    echo 'finished' . "\n";

    echo 'extract ' . $downloadName . "...\n";
    shell_exec('unzip -o ' . $downloadName);
    echo 'finished' . "\n";

    return $downloadName;
}

function installBranch($branch)
{
    $downloadName = 'branch.zip';
    $downloadUrl = 'https://github.com/typearea/dembelo/archive/'.$branch.'.zip';

    echo 'download ' . $downloadUrl . "...\n";
    shell_exec('wget -q -O branch.zip ' . $downloadUrl);
    echo 'finished' . "\n";

    echo 'extract ' . $downloadName . "...\n";
    shell_exec('unzip -o ' . $downloadName);
    echo 'finished' . "\n";

    echo 'move files...'."\n";
    shell_exec('mv dembelo-'.str_replace('/', '-', $branch).'/* .');
    shell_exec('rm dembelo-'.str_replace('/', '-', $branch).'/.gitignore');
    shell_exec('rm dembelo-'.str_replace('/', '-', $branch).'/.travis.yml');
    shell_exec('rmdir dembelo-'.str_replace('/', '-', $branch));
    echo 'finished' . "\n";

    echo "package installation...\n";
    shell_exec('composer --working-dir=www/ -n install');
    echo 'finished' . "\n";

    return $downloadName;
}
