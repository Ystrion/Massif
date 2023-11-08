<?php

declare(strict_types=1);

namespace Application\Controllers;

use DI\Attribute\Inject;
use DI\Container;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Twig\Environment;

abstract class AbstractController
{
    #[Inject]
    protected Container $container;

    #[Inject]
    protected ResponseFactoryInterface $responseFactory;

    #[Inject]
    protected Environment $twig;

    protected function redirect(string $url, int $statusCode = 302): ResponseInterface
    {
        return $this->responseFactory->createResponse($statusCode)->withHeader('Location', $url);
    }

    /**
     * @param array<string, mixed> $params
     */
    protected function render(string $path, array $params = []): string
    {
        return $this->twig->render($path, $params);
    }
}
