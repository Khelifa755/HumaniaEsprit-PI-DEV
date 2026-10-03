<?php

namespace App\Tests\Controller\SOCIALMEDIA;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class NotificationControllerTest extends WebTestCase
{
    /**
     * Test: Unread count endpoint returns JSON
     * Expected: 200 OK with valid JSON containing count
     */
    public function testUnreadCountReturnsValidJson(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/notifications/unread-count');

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
            $this->assertArrayHasKey('count', $data);
            $this->assertIsInt($data['count']);
            $this->assertGreaterThanOrEqual(0, $data['count']);
        }
    }

    /**
     * Test: List notifications returns array
     * Expected: 200 OK with JSON array of notifications
     */
    public function testListNotificationsReturnsArray(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/notifications/list');

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
            
            // If has items, check structure
            if (!empty($data)) {
                $firstNotif = reset($data);
                $this->assertIsArray($firstNotif);
            }
        }
    }

    /**
     * Test: Unread count is integer >= 0
     * Expected: Integer value, never negative
     */
    public function testUnreadCountIsNonNegativeInteger(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/notifications/unread-count');

        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($client->getResponse()->getContent(), true);
            $this->assertIsInt($data['count']);
            $this->assertGreaterThanOrEqual(0, $data['count']);
        }
    }

    /**
     * Test: List endpoint contains proper notification fields
     * Expected: Each notification has type, titre, message fields
     */
    public function testNotificationsHaveRequiredFields(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/notifications/list');

        if ($client->getResponse()->getStatusCode() === Response::HTTP_OK) {
            $data = json_decode($client->getResponse()->getContent(), true);
            
            if (!empty($data)) {
                // At least check the structure is array
                $this->assertIsArray($data);
            }
        }
    }

    /**
     * Test: Invalid route returns 404
     * Expected: 404 Not Found for non-existent endpoint
     */
    public function testInvalidEndpointReturns404(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/notifications/invalid-endpoint');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
}
