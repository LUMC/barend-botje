<?php
if (isset($_SERVER['HTTP_HOST'])) {
    header('Content-type: text/plain; charset=utf-8', true, 501);
    die('Please run this on the command line, not through the web server.');
}

if (!file_exists(__DIR__ . '/vendor/autoload.php')
    || !file_exists(__DIR__ . '/vendor/openai-php/client/src/OpenAI.php')) {
    die("Could not find the OpenAI client. Did you run 'composer install'?\n");
}

if (!file_exists('settings.json')) {
    $b = @file_put_contents('settings.json', json_encode([]));
    if (!$b) {
        die('Could not create the settings.json file.');
    } else {
        print("Created an empty settings.json file.\n");
    }
}
if (!is_readable('settings.json')) {
    die('Could not read the settings.json file.');
}
if (!is_writable('settings.json')) {
    die('Could not write the settings.json file.');
}

$_CONFIG = json_decode(file_get_contents('settings.json'), true);
