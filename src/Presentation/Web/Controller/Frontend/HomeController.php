<?php

namespace Groshy\Presentation\Web\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'groshy_frontend_home_home')]
    public function homeAction(): Response
    {
        return $this->render('home/home.html.twig', []);
    }
}
