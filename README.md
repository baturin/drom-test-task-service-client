Library to work with example comments API.

Example usage with Guzzle HTTP library:
```php
$httpClient = new GuzzleHttp\Client(
    ['base_uri' => 'https://example.com/']
);
$apiClient = new ExampleComApi($httpClient);

// Get all comments
$apiClient->getComments();

// Add a new comment
$newComment = $apiClient->addComment('John', 'Hi there!');

// Modify an existing comment
$updatedComment = $apiClient->updateComment($newComment->id, text: 'Hello world!');
```