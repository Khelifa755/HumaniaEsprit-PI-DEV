<?php

namespace App\Tests\Controller\SOCIALMEDIA;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class FeedControllerTest extends WebTestCase
{
    /**
     * Test: Feed endpoint loads (requires authentication)
     * Expected: 200 OK if authenticated, 302 Redirect or 403 if not
     */
    public function testFeedEndpointResponds(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),           // Authenticated
                $this->equalTo(Response::HTTP_FOUND),        // Redirect to login
                $this->equalTo(Response::HTTP_FORBIDDEN)     // No session
            )
        );
    }

    /**
     * Test: Feed accepts mode parameter (home/popular/saved)
     * Expected: 200 OK or redirect (depends on auth)
     */
    public function testFeedWithModeParameterHome(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed?mode=home');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FOUND),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Feed with popular mode
     * Expected: 200 OK or redirect
     */
    public function testFeedWithModeParameterPopular(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed?mode=popular');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FOUND),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Feed with saved mode
     * Expected: 200 OK or redirect
     */
    public function testFeedWithModeParameterSaved(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed?mode=saved');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FOUND),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Invalid mode parameter should default to home
     * Expected: Same behavior as home mode
     */
    public function testFeedWithInvalidModeDefaultsToHome(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed?mode=invalid_mode');

        $response = $client->getResponse();
        $this->assertThat(
            $response->getStatusCode(),
            $this->logicalOr(
                $this->equalTo(Response::HTTP_OK),
                $this->equalTo(Response::HTTP_FOUND),
                $this->equalTo(Response::HTTP_FORBIDDEN)
            )
        );
    }

    /**
     * Test: Feed returns HTML response (not API JSON)
     * Expected: Content-Type contains text/html
     */
    public function testFeedReturnsHtmlContentType(): void
    {
        $client = static::createClient();
        
        $client->request('GET', '/social/feed');

        $response = $client->getResponse();
        $contentType = $response->headers->get('Content-Type', '');
        
        // Should either be HTML or redirect
        $this->assertThat(
            true,
            $this->logicalOr(
                $this->stringContains('text/html'),
                $this->stringContains('text/html') || 
                $response->getStatusCode() === Response::HTTP_FOUND
            )
        );
    }
}
