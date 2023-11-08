<?php

declare(strict_types=1);

namespace Application\Controllers;

use Ystrion\ViaRouter\Attributes\Route;

class DefaultController extends AbstractController
{
    #[Route('homepage', '/')]
    public function homepage(): string
    {
        return $this->render('pages/homepage.twig');
    }
}
