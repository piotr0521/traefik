<?php

declare(strict_types=1);

namespace Groshy\Presentation\Web\Controller\Spa;

use Groshy\Presentation\Web\Controller\CustomerRequiredInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class SpaController extends AbstractController implements CustomerRequiredInterface
{
    #[Route('/{route}', name: 'groshy_spa_spa_catchall', requirements: ['route' => '.*'], priority: 9999)]
    public function catchAllAction(Request $request): Response
    {
        return $this->render('app.html.twig');
    }
}
