<?php
namespace Learnosity\Schemas;

require_once "./vendor/autoload.php";
use LearnositySdk\Request\DataApi;
use LearnositySdk\Request\Remote;

class QuestionData
{
    private array $schemas;

    public function __construct(string $patch = "")
    {
        $this->schemas = $this->downloadSchemas($patch);
    }

    public function getSchemas(array $questionTypes = []):array
    {
        if (empty($questionTypes)) {
            return $this->schemas;
        } else {
            $ret = [];
            foreach ($questionTypes as $type) {
                $ret[$type] = $this->schemas[$type];
            }
            return $ret;
        }
    }

    public function downloadSchemas($patch = ""):array
    {
        if (!file_exists('credentials.json')) {
            die(
                <<<ERR
				\n 	Create a credentials.json file and place it in the same directory as this file.

					Format:
					{
						"consumer_key": "the key",
						"consumer_secret": "the secret"
					}

				ERR
            );
        }
        $credentials = json_decode(file_get_contents('credentials.json'), true);
        fwrite(STDERR, "Downloading Question Data... ");
        $domain = 'localhost';
        $endpoint = 'https://schemas.learnosity.com/latest/questions/responses/editorV3';
        if ($patch != "") {
            $endpoint .= "/{$patch}";
        }

        $security = [
            'domain' => $domain,
            'consumer_key' => $credentials['consumer_key']
        ];
        $DataApi = new DataApi();

        $res = $DataApi->request(
            $endpoint,
            $security,
            $credentials['consumer_secret'],
            []
        );
        fwrite(STDERR, "Done.\n\n");
        $schemas = $res->json()['data'];
        foreach ($schemas as $key => $value) {
            if (preg_match('/.*(dev|test).*/', $key)) {
                unset($schemas[$key]);
            }
        }
        return $schemas;
    }
}
