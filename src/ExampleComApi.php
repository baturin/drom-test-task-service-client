<?php

namespace ExampleComApi;

use ExampleComApi\Entity\Comment;
use ExampleComApi\Exception\BadResponseJsonException;
use ExampleComApi\Exception\BadResponseHttpCodeException;
use ExampleComApi\Exception\BadResponseDataStructureException;
use ExampleComApi\Exception\NothingToUpdateException;
use ExampleComApi\Utils\ResponseBodyReader;
use Psr\Http\Client\ClientInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\StreamInterface;

class ExampleComApi
{
    private ResponseBodyReader $responseBodyReader;

    public function __construct(
        private ClientInterface $httpClient
    ) {
        $this->responseBodyReader = new ResponseBodyReader();
    }

    /**
     * @throws BadResponseHttpCodeException
     * @throws BadResponseJsonException
     * @throws BadResponseDataStructureException
     * 
     * @return Comment[]
     */
    public function getComments(): array
    {
        $response = $this->httpClient->sendRequest(
            new Request('GET', 'comments')
        );

        $statusCode = $response->getStatusCode();
        $this->checkResponseCode($statusCode, 200);

        $body = $response->getBody();
        $data = $this->decodeJson($body);
        return $this->responseBodyReader->readCommentList($data);
    }

    /**
     * @throws BadResponseHttpCodeException
     * @throws BadResponseJsonException
     * @throws BadResponseDataStructureException
     */
    public function addComment(string $name, string $text): Comment
    {
        $requestBody = json_encode([
            'name' => $name,
            'text' => $text,
        ]);
        $response = $this->httpClient->sendRequest(
            new Request(
                'POST', 
                'comment', 
                headers: ['Content-type' => 'application/json'], 
                body: $requestBody
            )
        );

        $statusCode = $response->getStatusCode();
        $this->checkResponseCode($statusCode, 201);

        $body = $response->getBody();
        $data = $this->decodeJson($body);
        return $this->responseBodyReader->readComment($data);
    }

    /**
     * @throws BadResponseHttpCodeException
     * @throws BadResponseJsonException
     * @throws BadResponseDataStructureException
     * @throws NothingToUpdateException
     */
    public function updateComment(int $id, ?string $name=null, ?string $text=null): Comment
    {
        if ($name === null && $text === null) {
            throw new NothingToUpdateException('Specify either name or a text to update');
        }

        $requestData = [];
        if ($name !== null) {
            $requestData['name'] = $name;
        }
        if ($text !== null) {
            $requestData['text'] = $text;
        }

        $requestBody = json_encode($requestData);
        $response = $this->httpClient->sendRequest(
            new Request(
                'PUT', 
                "comment/$id", 
                headers: ['Content-type' => 'application/json'], 
                body: $requestBody
            )
        );

        $statusCode = $response->getStatusCode();
        $this->checkResponseCode($statusCode, 200);

        $body = $response->getBody();
        $data = $this->decodeJson($body);
        return $this->responseBodyReader->readComment($data);
    }

    private function checkResponseCode(int $actualCode, int $expectedCode): void
    {
        if ($actualCode !== $expectedCode) {
            throw new BadResponseHttpCodeException(
                "Server returned unexpected HTTP status code. " .
                "Expected code: $expectedCode. " . 
                "Actual code: $actualCode"
            );
        }
    }

    private function decodeJson(StreamInterface $body): mixed {
        try {
            return json_decode(
                $body->getContents(), 
                associative: true, 
                flags: JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            throw new BadResponseJsonException(
                'Invalid response: invalid JSON in response body', 
                previous: $e
            );
        }
    }
}
