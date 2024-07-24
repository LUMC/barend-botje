<?php
@ini_set('session.use_cookies', 1);
@ini_set('session.use_only_cookies', 1);
@session_start();
$tStart = microtime(true);

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



// Now, send the user's question over and wait for the reply.
try {
    // Send off the question. We could create a message and then create a run, but doing it in one go is easier.
    // This is asynchronous; we'll get an instant reply, but the run isn't done yet.
    $aRun = $_API->threads()->runs()->create(
        threadId: $_SESSION['thread'],
        parameters: [
            'assistant_id' => $_CONFIG['assistant'],
            'additional_messages' => [
                [
                    'role' => 'user',
                    'content' => $_POST['input'],
                ],
            ]
        ],
    )->toArray();
    $nRunID = $aRun['id'];

} catch (Exception $e) {
    die(json_encode(array_merge($a, ['errors' => ['ENEWMESSAGE' => "(Barend can't hear you at the moment.)<br>" . htmlspecialchars($e->getMessage())]])));
}



// Now, we wait. We have stored the thread, so even if the user refreshes the page, nothing will be lost.
// So, wait for ten seconds max. Then ask the user to refresh the page.
while ((microtime(true) - $tStart) < 15) {
    // Start with a sleep; we don't want to stress the OpenAI server out.
    sleep(1);
    try {
        $aRun = $_API->threads()->runs()->retrieve(
            threadId: $_SESSION['thread'],
            runId: $nRunID,
        )->toArray();
    } catch (Exception $e) {
        // Also, silently ignore errors.
        break;
    }

    if ($aRun['status'] == 'completed') {
        // Done!
        // Retrieve the last 3 messages and check the run ID. To just take one might also work, but still.
        try {
            $aMessages = $_API->threads()->messages()->list(
                $_SESSION['thread'],
                [
                    'limit' => 3,
                ]
            );
            foreach ($aMessages['data'] as $aMessage) {
                if ($aMessage['run_id'] != $nRunID || $aMessage['role'] != 'assistant') {
                    continue;
                }
                $aContentParts = [];
                foreach ($aMessage['content'] as $aContent) {
                    if ($aContent['type'] == 'text') {
                        $aContentParts[] = [
                            'role' => $aMessage['role'],
                            'content' => htmlspecialchars($aContent['text']['value']),
                            'created_at' => $aMessage['created_at'],
                        ];
                    }
                }
                $a['data'] = array_merge($aContentParts, $a['data']);
            }

        } catch (Exception $e) {
            die(json_encode(array_merge($a, ['errors' => ['EMESSAGELIST' => "It was on the tip of my tongue... but I totally forgot what I was about to say.<br>" . htmlspecialchars($e->getMessage())]])));
        }
        die(json_encode($a));
    }
}



// If we get here, it didn't finish in time, or we kept getting errors.
die(json_encode(array_merge($a, ['errors' => ['ETIMEOUT' => "It's taking too long for me to gather my thoughts... maybe you can try and refresh the page while I go and get some coffee."]])));
?>
