<?php
@ini_set('session.use_cookies', 1);
@ini_set('session.use_only_cookies', 1);
@session_start();

header('Content-Type: application/json; charset=utf-8');
$a = [
    'errors' => [],
    'warnings' => [],
    'messages' => [],
    'data' => [],
];
$aInitMessages = [
    [
        'role' => 'assistant',
        'content' => "This is Barend speaking. Barend Botje. Ask me anything!",
        'created_at' => time(), // We store everything in UTC.
    ],
    [
        'role' => 'assistant',
        'content' => "I love talking to you about all things FAIR. I'm always positive. In fact, I'm overly positive! The world is at our feet.",
        'created_at' => time(), // We store everything in UTC.
    ],
    [
        'role' => 'assistant',
        'content' => "Just ask me anything, and I'll tell you all about it.",
        'created_at' => time(), // We store everything in UTC.
    ],
];

$_CONFIG = @json_decode(file_get_contents('settings.json'), true);
if (count(array_intersect_key($_CONFIG, array_flip(['api_key', 'model', 'assistant']))) < 3) {
    die(json_encode(array_merge($a, ['errors' => ['ENOCONFIG' => 'Configuration of this app was not complete. Run the configuration first.']])));
}

if (empty($_POST['input'])) {
    if (!empty($_GET['input'])) {
        $_POST['input'] = $_GET['input'];
    } else {
        die(json_encode(array_merge($a, ['errors' => ['ENODATA' => "I can't hear you very well. Could you speak up?"]])));
    }
} else {
    $_POST['input'] = trim($_POST['input']);
}



require __DIR__ . '/vendor/autoload.php';
try {
    $_API = OpenAI::client($_CONFIG['api_key']);
} catch (Exception $e) {
    die(json_encode(array_merge($a, ['errors' => ['ECONNECTION' => "My brain is a bit foggy at the moment, I can't think very well.<br>" . htmlspecialchars($e->getMessage())]])));
}





if ($_POST['input'] == '#init') {
    // Init.
    sleep(2);
    die(json_encode(array_merge($a, ['data' => $aInitMessages])));
}



// We're here for a new question.
// Check if we have a thread. If not, create one and pre-seed the conversation.
if (empty($_SESSION['thread'])) {
    try {
        // Init the thread with Barend's messages.
        $aThread = $_API->threads()->create(
            [
                'messages' => array_map(
                    function ($aMessage)
                    {
                        return array_intersect_key($aMessage, array_flip(['role', 'content']));
                    },
                    $aInitMessages
                ),
            ]
        )->toArray();
    } catch (Exception $e) {
        die(json_encode(array_merge($a, ['errors' => ['ENEWTHREAD' => "I don't know what's going on, perhaps too many people are talking to me at the moment, but I can't seem to find my brain space to start a new conversation.<br>" . htmlspecialchars($e->getMessage())]])));
    }

    $_SESSION['thread'] = $aThread['id'];
}
?>
