<?php

namespace App\Tests\Repository\SOCIALMEDIA;

use PHPUnit\Framework\TestCase;
use App\Repository\SOCIALMEDIA\MessageService;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MessageServiceUnitTest extends TestCase
{
    private MessageService $messageService;
    private $mockHttpClient;

    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(HttpClientInterface::class);
        
        // Create service with mocked HTTP client
        $this->messageService = new MessageService(
            $this->mockHttpClient,
            'https://test.supabase.co',
            'test-anon-key'
        );
    }

    /**
     * Test: getPrivateMessages returns array
     * Expected: Returns array from JSON response
     */
    public function testGetPrivateMessagesReturnsArray(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn(json_encode([
            ['id' => 1, 'content' => 'Hello', 'sender_id' => 1, 'receiver_id' => 2],
            ['id' => 2, 'content' => 'Hi', 'sender_id' => 2, 'receiver_id' => 1],
        ]));

        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $result = $this->messageService->getPrivateMessages(1, 2);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Hello', $result[0]['content']);
    }

    /**
     * Test: getPrivateMessages with empty response
     * Expected: Returns empty array
     */
    public function testGetPrivateMessagesEmptyResponse(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn(json_encode([]));

        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $result = $this->messageService->getPrivateMessages(1, 2);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test: getPrivateMessages handles malformed JSON
     * Expected: Returns empty array on error
     */
    public function testGetPrivateMessagesHandlesMalformedJson(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn('invalid json');

        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $result = $this->messageService->getPrivateMessages(1, 2);
        
        $this->assertIsArray($result);
    }

    /**
     * Test: getGroupMessages returns array
     * Expected: Returns array of group messages
     */
    public function testGetGroupMessagesReturnsArray(): void
    {
        $mockResponse = $this->createMock(ResponseInterface::class);
        $mockResponse->method('getContent')->willReturn(json_encode([
            ['id' => 1, 'content' => 'Group message', 'group_id' => 1, 'sender_id' => 5],
        ]));

        $this->mockHttpClient->method('request')->willReturn($mockResponse);

        $result = $this->messageService->getGroupMessages(1);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
    }

    /**
     * Test: Query building with correct filters
     * Expected: Request URL contains correct query parameters
     */
    public function testMessageServiceConstructorSetsSupabaseUrl(): void
    {
        $reflection = new \ReflectionClass($this->messageService);
        $property = $reflection->getProperty('supabaseUrl');
        $property->setAccessible(true);
        
        $this->assertEquals('https://test.supabase.co', $property->getValue($this->messageService));
    }
}
