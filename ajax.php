<?php
header('Content-Type: application/json; charset=utf-8');
$a = [
    'errors' => [],
    'warnings' => [],
    'messages' => [],
    'data' => [],
];

if (empty($_POST['input'])) {
    die(json_encode(array_merge($a, ['errors' => ['ENODATA' => 'No data was received.']])));
}

sleep(2);

die(json_encode($a));
?>
