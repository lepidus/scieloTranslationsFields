<?php

namespace APP\plugins\generic\scieloTranslationsFields\classes\clients;

use APP\core\Application;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;

class DoiClient
{
    private $guzzleClient;

    public const DOI_URL = 'https://doi.org/';

    public function __construct($guzzleClient = null)
    {
        if (!is_null($guzzleClient)) {
            $this->guzzleClient = $guzzleClient;
        } else {
            $this->guzzleClient = Application::get()->getHttpClient();
        }
    }

    public function getApaCitation(string $doi): ?string
    {
        $doiUrl = self::DOI_URL . $doi;

        try {
            $response = $this->guzzleClient->request('GET', $doiUrl, [
                'headers' => [
                    'Accept' => 'text/x-bibliography; style=apa'
                ],
            ]);

            return $response->getBody()->getContents();
        } catch (ClientException $e) {
            $errorMsg = $e->getResponse()->getBody()->getContents();
            error_log("Error while getting DOI citation: $errorMsg");
        }

        return null;
    }
}
