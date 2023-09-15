<?php

namespace Groshy\Presentation\Web\Controller\Frontend;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContentController extends AbstractController
{
    #[Route(path: '/terms', name: 'groshy_frontend_content_terms')]
    public function termsAction(): Response
    {
        return $this->render('content/terms.html.twig', []);
    }

    #[Route(path: '/privacy-policy', name: 'groshy_frontend_content_policy')]
    public function policyAction(): Response
    {
        return $this->render('content/policy.html.twig', []);
    }

    #[Route(path: '/security', name: 'groshy_frontend_content_security')]
    public function securityAction(): Response
    {
        $faqs = [
            [
                'question' => 'Is Groshy.io safe?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Is Groshy.io secure?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How do I know if I’ve used Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What banks use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Does Groshy.io sell my data?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What apps use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How does Groshy.io work for consumers?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
        ];

        return $this->render('content/security.html.twig', [
            'faqs' => $faqs,
        ]);
    }

    #[Route(path: '/features', name: 'groshy_frontend_content_features')]
    public function featuresAction(): Response
    {
        $features = [
            [
                'title' => 'Automate administration',
                'items' => [
                    [
                        'name' => 'Investment account integration',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Transaction tracking',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Crypto account integration',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                ],
            ],
            [
                'title' => 'Reporting',
                'items' => [
                    [
                        'name' => 'Performance reporting',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Cash flow management',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Target vs actual performance',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                ],
            ],
            [
                'title' => 'Powerful portfolio analysis tools',
                'items' => [
                    [
                        'name' => 'Chart your entire portfolio',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'View your allocation',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Historical performance',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                ],
            ],
            [
                'title' => 'Chart your net worth progress over time',
                'items' => [
                    [
                        'name' => 'View your balance history',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Track real estate with Groshy.io',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                    [
                        'name' => 'Manually track any asset',
                        'description' => 'Purus lacus sit purus quisque id est. Lectus phasellus et malesuada volutpat congue et praesent. Tellus vestibulum pharetra amet euismod in interdum. Blandit aliquet justo.',
                    ],
                ],
            ],
        ];

        $faqs = [
            [
                'question' => 'Is Groshy.io safe?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Is Groshy.io secure?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How do I know if I’ve used Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What banks use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Does Groshy.io sell my data?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What apps use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How does Groshy.io work for consumers?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
        ];

        return $this->render('content/features.html.twig', [
            'features' => $features,
            'faqs' => $faqs,
        ]);
    }

    #[Route(path: '/pricing', name: 'groshy_frontend_content_pricing')]
    public function pricingAction(): Response
    {
        $faqs = [
            [
                'question' => 'Is Groshy.io safe?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Is Groshy.io secure?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How do I know if I’ve used Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What banks use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'Does Groshy.io sell my data?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'What apps use Groshy.io?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
            [
                'question' => 'How does Groshy.io work for consumers?',
                'answer' => '<p class="text-sm md:text-base font-thin text-slate-900">Dolor amet auctor netus vehicula blandit. Amet lacinia nunc sodales odio aliquet ultrices ligula. Massa id lacus justo platea nisl in. Eu sed quis sed nisl. Nunc pulvinar nisl suscipit nisl neque donec tristique. Lacus parturient sed senectus orci orci integer morbi. Tortor ullamcorper bibendum pellentesque aliquam consectetur.</p>',
            ],
        ];

        return $this->render('content/pricing.html.twig', [
            'faqs' => $faqs,
        ]);
    }
}
