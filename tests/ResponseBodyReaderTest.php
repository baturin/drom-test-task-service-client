<?php

use PHPUnit\Framework\TestCase;
use ExampleComApi\Exception\BadResponseDataStructureException;
use ExampleComApi\Utils\ResponseBodyReader;

final class ResponseBodyReaderTest extends TestCase
{
    public function testReadCommentsListBadRootType(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readCommentList("str");
    }

    public function testReadCommentsListBadChildType(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readCommentList(["str1", "str2"]);
    }

    public function testReadCommentBadRoot(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment(
            "str" // string instead of an object
        );
    }

    public function testReadCommentBadId(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            'id' => "1", // string instead of a number
            'name' => 'John',
            'text' => 'Hi there'
        ]);
    }

    public function testReadCommentBadName(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            'id' => 1, 
            'name' => 1, // number instead of a string
            'text' => 'Hi there'
        ]);
    }

    public function testReadCommentBadText(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            'id' => 1, 
            'name' => 'John', 
            'text' => 1 // number instead of a string
        ]);
    }

    public function testReadCommentAbsentId(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            // 'id' field is absent
            'name' => 'John', 
            'text' => 'Hi there'
        ]);
    }

    public function testReadCommentAbsentName(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            'id' => 1,
            // 'name' field is absent
            'text' => 'Hi there'
        ]);
    }

    public function testReadCommentAbsentText(): void
    {
        $responseBodyReader = new ResponseBodyReader();
        $this->expectException(BadResponseDataStructureException::class);
        $responseBodyReader->readComment([
            'id' => 1,
            'name' => 'John', 
            // 'text' field is absent
        ]);
    }
}
