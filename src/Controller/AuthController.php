<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthController extends AbstractController implements
    AuthenticationEntryPointInterface
{
    public function start(
        Request $request,
        ?AuthenticationException $authException = null
    ): Response {
        return $this->render('auth.html.twig');
    }
}
