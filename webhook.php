<?php
require_once __DIR__.'/vendor/autoload.php';

use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

if (!file_exists(__DIR__.'/config.yml')) {
    echo "Please, define your satis configuration in a config.yml file.\nYou can use the config.yml.dist as a template.";
    exit(-1);
}

$defaults = array(
    'bin' => 'bin/satis',
    'json' => 'satis.json',
    'webroot' => 'web/',
    'user' => null,
);
$config = Yaml::parse(__DIR__.'/config.yml');
$config = array_merge($defaults, $config);

$errors = array();
if (!file_exists($config['bin'])) {
    $errors[] = 'The Satis bin could not be found.';
}

if (!file_exists($config['json'])) {
    $errors[] = 'The satis.json file could not be found.';
}

if (!file_exists($config['webroot'])) {
    $errors[] = 'The webroot directory could not be found.';
}

if (!empty($errors)) {
    echo 'The build cannot be run due to some errors. Please, review them and check your config.yml:'."\n";
    foreach ($errors as $error) {
        echo '- '.$error."\n";
    }
    exit(-1);
}

$command = sprintf('%s build %s %s', $config['bin'], $config['json'], $config['webroot']);
if (null !== $config['user']) {
    $command = sprintf('sudo -u %s -i %s', $config['user'], $command);
}

$process = new Process($command);
$exitCode = $process->run(function ($type, $buffer) {
    if ('err' === $type) {
        echo 'E';
    } else {
        echo '.';
    }
});

echo "\n\n" . ($exitCode === 0 ? 'Successful rebuild! ☺' : 'Oops! An error occured! ☹') . "\n";
