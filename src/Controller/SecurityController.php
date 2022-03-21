<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use \Symfony\Component\HttpFoundation\RedirectResponse;

class SecurityController extends AbstractController
{
    /**
     * @Route("/auth", name="github_redirect_url")
     */
    public function adminAuthAction(): RedirectResponse
    {
        // To avoid the ?code= url. Can be done with Javascript.
        return $this->redirectToRoute('diary');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
    }
}