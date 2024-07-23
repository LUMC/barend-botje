<?php
header('Content-Type: application/json; charset=utf-8');
$a = [
    'errors' => [],
    'warnings' => [],
    'messages' => [],
    'data' => [],
];

$_CONFIG = @json_decode(file_get_contents('settings.json'), true);
if (count(array_intersect_key($_CONFIG, array_flip(['api_key', 'model', 'assistant']))) < 3) {
    die(json_encode(array_merge($a, ['errors' => ['ENOCONFIG' => 'Configuration of this app was not complete. Run the configuration first.']])));
}

if (empty($_POST['input'])) {
    die(json_encode(array_merge($a, ['errors' => ['ENODATA' => 'No data was received.']])));
}

sleep(2);

if ($_POST['input'] == '#intro') {
    $a['data'][] = "This is Barend speaking. Barend Botje. Ask me anything!";
    $a['data'][] = "I love talking to you about all things FAIR. I'm always positive. In fact, I'm overly positive! The world is at our feet.";
    $a['data'][] = "Just ask me anything, and I'll tell you all about it.";
}

die(json_encode($a));
?>
