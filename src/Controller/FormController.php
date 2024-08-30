<?php

namespace App\Controller;

use App\Form\DoubleSubmitApproachFormType;
use App\Form\LazyApproachFormType;
use App\Service\CsrfLazyTokenManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FormController extends AbstractController
{
    protected CsrfLazyTokenManager $csrfLazyTokenManager;

    public function __construct(CsrfLazyTokenManager $csrfLazyTokenManager)
    {
        $this->csrfLazyTokenManager = $csrfLazyTokenManager;
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('form/index.html.twig');
    }

    #[Route('/approach/lazy', name: 'approach_lazy')]
    public function lazyApproach(Request $request): Response
    {
        $csrfTokenLazy = !$request->hasPreviousSession();

        if ($csrfTokenLazy && $request->isMethod('POST')
            && $request->isXmlHttpRequest()
            && $request->request->has('token')) {
            $response = new Response($this->csrfLazyTokenManager->getToken('lazy_form_token')->getValue());
            $response->headers->set('Content-Type', 'text/plain');
            return $response;
        }

        $this->csrfLazyTokenManager->setLazy($csrfTokenLazy);

        $form = $this->createForm(LazyApproachFormType::class, null, [
            'csrf_token_manager' => $this->csrfLazyTokenManager,
            'csrf_token_id' => 'lazy_form_token',
        ]);

        $form->handleRequest($request);

        $name = null;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $name = $form->get('name')->getData();
            } else {
                $this->csrfLazyTokenManager->setLazy(false);
            }
        }

        return $this->render('form/lazy.html.twig', [
            'form' => $form->createView(),
            'name' => $name
        ]);
    }

    #[Route('/approach/double-submit', name: 'approach_double_submit')]
    public function doubleSubmitApproach(Request $request): Response
    {
        $csrfTokenLazy = !$request->hasPreviousSession();

        $this->csrfLazyTokenManager->setLazy($csrfTokenLazy);

        $form = $this->createForm(DoubleSubmitApproachFormType::class, null, [
            'csrf_token_manager' => $this->csrfLazyTokenManager,
            'csrf_token_lazy' => $csrfTokenLazy && $request->isMethod('GET'),
            'csrf_token_id' => 'lazy_form_token',
        ]);

        $form->handleRequest($request);

        $name = null;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $name = $form->get('name')->getData();
            } else {
                $this->csrfLazyTokenManager->setLazy(false);
            }
        }

        return $this->render('form/double-submit.html.twig', [
            'form' => $form->createView(),
            'name' => $name
        ]);
    }
}
