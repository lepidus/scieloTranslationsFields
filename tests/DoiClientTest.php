<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use APP\submission\Submission;
use APP\publication\Publication;
use APP\facades\Repo;
use APP\plugins\reports\scieloTranslationsFields\classes\clients\DoiClient;

class DoiClientTest extends TestCase
{
    private $mockGuzzleClient;
    private $doiClient;
    private $originalDocumentDoi = '10.1590/0037-8682-0167-2020';
    private $expectedDoiCitation = 'Croda, J., Oliveira, W. K. de, Frutuoso, R. L., Mandetta, L. H., 
        Baia-da-Silva, D. C., Brito-Sousa, J. D., Monteiro, W. M., & Lacerda, M. V. G. (2020). 
        COVID-19 in Brazil: advantages of a socialized unified health system and preparation to contain cases. 
        Revista Da Sociedade Brasileira de Medicina Tropical, 53. https://doi.org/10.1590/0037-8682-0167-2020';

    public function setUp(): void
    {
        $this->mockGuzzleClient = $this->createMockGuzzleClient();
        $this->doiClient = new DoiClient($this->mockGuzzleClient);
    }

    private function createMockGuzzleClient()
    {
        $mockResponse = new Response(200, [], $this->expectedDoiCitation);
        $mockHandler = new MockHandler($mockResponse);
        $guzzleClient = new Client(['handler' => $mockHandler]);

        return $guzzleClient;
    }

    public function testGetDoiApaCitation()
    {
        $doiApaCitation = $this->doiClient->getApaCitation($this->originalDocumentDoi);

        $this->assertEquals($this->expectedDoiCitation, $doiApaCitation);
    }
}
