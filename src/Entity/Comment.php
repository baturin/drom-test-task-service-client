<?php

namespace ExampleComApi\Entity;

class Comment {
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $text
    ) {
    }
}