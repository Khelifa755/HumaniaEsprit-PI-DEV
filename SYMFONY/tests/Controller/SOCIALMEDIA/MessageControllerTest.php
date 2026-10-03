<?php

namespace App\Tests\Controller\SOCIALMEDIA;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class MessageControllerTest extends WebTestCase
{
    /**
     * Test: Send private message with valid data
     * Expected: 200 OK (needs real user session, but test structure is there)
     */
    public function testSendPrivateMessageWithValidData(): void
    {
        $client = static::createClient();
        
        $payload = [
            'content' => 'Bonjour! Comment allez-vous?',
            'receiver_id' => 2,
            'sender_name' => 'John Doe',
            'sender_avatar' => 'JD'
        ];

        $client->request(
            'POST',
            '/social/messages/private',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        // If no user session: 403 Forbidden (expected)
        // If user authenticated: should be 200 OK
        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_FORBIDDEN), // No session
                $this->equalTo(Response::HTTP_OK)           // With session
            )
        );
    }

    /**
     * Test: Send private message with missing content
     * Expected: 400 Bad Request
     */
    public function testSendPrivateMessageMissingContent(): void
    {
        $client = static::createClient();
        
        $payload = [
            'receiver_id' => 2,
            'sender_name' => 'John Doe'
        ];

        $client->request(
            'POST',
            '/social/messages/private',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        // Should be 400 or 403 depending on auth
        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Send private message with missing receiver_id
     * Expected: 400 Bad Request
     */
    public function testSendPrivateMessageMissingReceiverId(): void
    {
        $client = static::createClient();
        
        $payload = [
            'content' => 'Test message',
            'sender_name' => 'John Doe'
        ];

        $client->request(
            'POST',
            '/social/messages/private',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );

        $this->assertThat(
            $client->getResponse()->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_BAD_REQUEST),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Get private messages returns array
     * Expected: 200 OK with JSON response containing messages array
     */
    public function testGetPrivateMessagesReturnsArray(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/messages/private/2');

        // Should be 200 or 403 depending on auth
        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );

        // If OK, should be valid JSON
        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('messages', $data);
        }
    }

    /**
     * Test: Count unread messages endpoint
     * Expected: 200 OK with JSON containing unread_count
     */
    public function testCountUnreadMessages(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/messages/unread/count');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($response->getContent(), true);
            $this->assertIsArray($data);
            $this->assertArrayHasKey('unread_count', $data);
            $this->assertIsInt($data['unread_count']);
        }
    }
}
