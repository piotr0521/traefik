<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Presentation\Web\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class ContentControllerTest extends WebTestCase
{
    private ?KernelBrowser $client;

    private RouterInterface $router;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = $this->client->getContainer()->get(RouterInterface::class);
    }

    /**
     * @test
     */
    public function it_gets_policy_page_without_error()
    {
        $this->client->request('GET', $this->router->generate('groshy_frontend_content_policy'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_gets_terms_page_without_error()
    {
        $this->client->request('GET', $this->router->generate('groshy_frontend_content_terms'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
