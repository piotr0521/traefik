<?php

namespace Groshy\Presentation\Web\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    #[Route(path: '', name: 'groshy_frontend_checkout_checkout')]
    public function checkoutAction(): Response
    {
        return $this->render('checkout/checkout.html.twig', []);
    }
}
