<?php

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use ExampleComApi\ExampleComApi;
use ExampleComApi\Exception\BadResponseJsonException;
use ExampleComApi\Exception\BadResponseHttpCodeException;
use ExampleComApi\Exception\NothingToUpdateException;
use ExampleComApi\Entity\Comment;

final class ExampleComApiTest extends TestCase
{
    public function testGetCommentsSuccess(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            new Response(200, body: json_encode([
                [
                    'id' => 1,
                    'name' => 'John',
                    'text' => 'Hi there'
                ],
                [
                    'id' => 2,
                    'name' => 'Tom',
                    'text' => 'Hello world'
                ]
            ])),
            expectedMethod: 'GET',
            expectedUriPath: 'comments'
        );
        $api = new ExampleComApi($httpClient);
        $comments = $api->getComments();

        $this->assertIsArray($comments);
        $this->assertCount(2, $comments);

        $this->assertComment(new Comment(1, 'John', 'Hi there'), $comments[0]);
        $this->assertComment(new Comment(2, 'Tom', 'Hello world'), $comments[1]);
    }

    public function testGetCommentsEmptySet(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            new Response(200, body: '[]')
        );
        $api = new ExampleComApi($httpClient);
        $comments = $api->getComments();

        $this->assertIsArray($comments);
        $this->assertCount(0, $comments);
    }

    public function testGetCommentsInvalidJson(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            // 200 code but invalid JSON in response body
            new Response(200, [], '---')
        );
        $api = new ExampleComApi($httpClient);
        
        $this->expectException(BadResponseJsonException::class);
        $api->getComments();
    }

    public function testGetCommentsBadHttpCode(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            new Response(500, [], '[]')
        );
        $api = new ExampleComApi($httpClient);
        
        $this->expectException(BadResponseHttpCodeException::class);
        $api->getComments();
    }

    public function testAddCommentSuccess(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            new Response(201, [], json_encode([
                'id' => 1,
                'name' => 'John',
                'text' => 'Hi there'
            ])),
            expectedMethod: 'POST',
            expectedUriPath: 'comment'
        );
        $api = new ExampleComApi($httpClient);
        $comment = $api->addComment('John', 'Hi there');
        $this->assertComment($comment, new Comment(1, 'John', 'Hi there'));
    }

    public function testUpdateCommentSuccess(): void
    {
        $httpClient = $this->createSingleRequestMockClient(
            new Response(200, [], json_encode([
                'id' => 1,
                'name' => 'John',
                'text' => 'Hi there'
            ])),
            expectedMethod: 'PUT',
            expectedUriPath: 'comment/1'
        );
        $api = new ExampleComApi($httpClient);
        $comment = $api->updateComment(1, 'John', 'Hi there');
        $this->assertComment(new Comment(1, 'John', 'Hi there'), $comment);
    }

    public function testUpdateCommentEmptyUpdateRequest(): void
    {
        $httpClient = $this->createEmptyRequestMockClient();
        $api = new ExampleComApi($httpClient);
        $this->expectException(NothingToUpdateException::class);
        $comment = $api->updateComment(1);
    }

    private function createEmptyRequestMockClient(): Client
    {
        $mock = new MockHandler([]);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    private function createSingleRequestMockClient(
        Response $mockResponse,
        ?string $expectedMethod = null,
        ?string $expectedUriPath = null,
    ): Client
    {
        $mock = new MockHandler([
            function (Request $request) use ($mockResponse, $expectedMethod, $expectedUriPath) {
                if ($expectedMethod !== null) {
                    $actualMethod = $request->getMethod();
                    $this->assertSame(
                        $expectedMethod, 
                        $actualMethod,
                        "Invalid HTTP method called"
                    );
                }

                if ($expectedUriPath !== null) {
                    $actualUriPath = $request->getUri()->getPath();
                    $this->assertSame(
                        $expectedUriPath, 
                        $actualUriPath,
                        "Invalid HTTP URI path called"
                    );
                }

                return $mockResponse;
            }
        ]);
        $handlerStack = HandlerStack::create($mock);
        return new Client(['handler' => $handlerStack]);
    }

    private function assertComment(Comment $expectedComment, Comment $actualComment): void
    {
        $this->assertSame($expectedComment->id, $actualComment->id);
        $this->assertSame($expectedComment->name, $actualComment->name);
        $this->assertSame($expectedComment->text, $actualComment->text);
    }
}
