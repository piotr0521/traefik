<?php

namespace Groshy\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;

class MenuBuilder
{
    private FactoryInterface $factory;

    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Features', ['route' => 'groshy_frontend_content_features']);
        $menu->addChild('Security', ['route' => 'groshy_frontend_content_security']);
        $menu->addChild('Pricing', ['route' => 'groshy_frontend_content_pricing']);

        return $menu;
    }
}
