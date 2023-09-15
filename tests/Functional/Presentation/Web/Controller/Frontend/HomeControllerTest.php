<?php

declare(strict_types=1);

namespace Groshy\Tests\Functional\Presentation\Web\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class HomeControllerTest extends WebTestCase
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
    public function it_gets_home_page_without_error()
    {
        $this->client->request('GET', $this->router->generate('groshy_frontend_home_home'));
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
