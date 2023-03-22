<?php

namespace ExampleComApi\Utils;

use ExampleComApi\Entity\Comment;
use ExampleComApi\Exception\BadResponseDataStructureException;

class ResponseBodyReader
{
    public function readComment(mixed $item): Comment
    {
        if (!is_array($item)) {
            throw new BadResponseDataStructureException(
                "Invalid response: expected object for comment item"
            );
        }

        $id = $item['id'] ?? null;
        if (!is_int($id)) {
            throw new BadResponseDataStructureException(
                "Invalid response: 'id' field is invalid or does not exist"
            );
        }
        $name = $item['name'] ?? null;
        if (!is_string($name)) {
            throw new BadResponseDataStructureException(
                "Invalid response: 'name' field is invalid or does not exist"
            );
        }
        $text = $item['text'] ?? null;
        if (!is_string($text)) {
            throw new BadResponseDataStructureException(
                "Invalid response: 'text' field is invalid or does not exist" 
            );
        }

        return new Comment(
            $id, $name, $text
        );
    }

    public function readCommentList(mixed $items)
    {
        if (!is_array($items)) {
            throw new BadResponseDataStructureException(
                'Invalid response: expected array at root level'
            );
        }

        foreach ($items as $key => $item) {
            if (!is_array($item)) {
                throw new BadResponseDataStructureException(
                    "Invalid response: item #$key must be an object"
                );
            }
        }

        return array_map($this->readComment(...), $items);
    }
}
