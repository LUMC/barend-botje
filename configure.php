<?php
use \OpenAI\Responses\Assistants\AssistantListResponse;
use \OpenAI\Responses\Models\ListResponse as ModelListResponse;
use \OpenAI\Testing\ClientFake;

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

require __DIR__ . '/vendor/autoload.php';

// Select the API key and retrieve the available models.
while (!isset($_CONFIG['api_key']) || !isset($_API) || empty($aModels)) {
    $sInput = ($_CONFIG['api_key'] ?? '');
    if (!$sInput) {
        print('Enter the OpenAI API key to use: ');
        $sInput = trim(fgets(STDIN));
    }
    if ($sInput) {
        try {
            if ($sInput == 'test') {
                // Debugging.
                $_API = new ClientFake(
                    [
                        ModelListResponse::fake(),
                        AssistantListResponse::fake(),
                    ]
                );
            } else {
                $_API = OpenAI::client($sInput);
            }
            $aModels = $_API->models()->list()->toArray();
            if ($aModels && isset($aModels['data'])) {
                $aModels = array_filter(
                    array_map(
                        function ($aModel)
                        {
                            return ($aModel['id'] ?? '');
                        }, $aModels['data']
                    )
                );
            } else {
                print("Could not find any available models.\n");
                $aModels = [];
                continue;
            }
        } catch (Exception $e) {
            print($e->getMessage() . "\n");
            continue;
        }

        // If we get here, we successfully connected.
        $_CONFIG['api_key'] = $sInput;
        @file_put_contents('settings.json', json_encode($_CONFIG, JSON_PRETTY_PRINT));
    }
}



// Select the model from the list of available models.
while (!isset($_CONFIG['model'])) {
    if (count($aModels) == 1) {
        $sInput = $aModels[0];
        print("Selecting model $sInput as it's the only one available.\n");
    } else {
        print('Available models: ' . implode(', ', $aModels) . ".\n" .
              'Select the model to use: ');
        $sInput = trim(fgets(STDIN));
    }
    if ($sInput && in_array($sInput, $aModels)) {
        $_CONFIG['model'] = $sInput;
        @file_put_contents('settings.json', json_encode($_CONFIG, JSON_PRETTY_PRINT));
    }
}



// Retrieve the list of assistants and select one.
while (!isset($_CONFIG['assistant'])) {
    if (empty($aAssistants)) {
        $aAssistants = $_API->assistants()->list()->toArray();
        if ($aAssistants && isset($aAssistants['data'])) {
            $aAssistants = array_filter(
                array_map(
                    function ($aAssistant)
                    {
                        return ($aAssistant['id'] ?? '');
                    }, $aAssistants['data']
                )
            );
        } else {
            $aAssistants = [];
        }

        if (!$aAssistants) {
            // FIXME: Create one?
            die("Could not find any available assistants.\n");
        }
    }

    if (count($aAssistants) == 1) {
        $sInput = $aAssistants[0];
        print("Selecting assistant $sInput as it's the only one available.\n");
    } else {
        print('Available assistants: ' . implode(', ', $aAssistants) . ".\n" .
              'Select the assistant to use: ');
        $sInput = trim(fgets(STDIN));
    }
    if ($sInput && in_array($sInput, $aAssistants)) {
        $_CONFIG['assistant'] = $sInput;
        @file_put_contents('settings.json', json_encode($_CONFIG, JSON_PRETTY_PRINT));
    }
}



// Re-read file so we handle not having been able to write.
print("Stored the following settings:\n");
$_CONFIG = json_decode(file_get_contents('settings.json'), true);
var_dump($_CONFIG);
