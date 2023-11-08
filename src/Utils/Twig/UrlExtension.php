<?php

declare(strict_types=1);

namespace Application\Utils\Twig;

use DI\Attribute\Inject;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Ystrion\ViaRouter\ViaRouter;

class UrlExtension extends AbstractExtension
{
    #[Inject]
    protected ViaRouter $router;

    /** @var array<string, string>|null $manifest */
    protected ?array $manifest = null;

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset', [$this, 'asset']),
            new TwigFunction('path', [$this, 'path'])
        ];
    }

    public function asset(string $path): string
    {
        if ($this->manifest === null) {
            $manifest = file_get_contents(__DIR__ . '/../../../public/assets/manifest.json');
            $manifest = is_string($manifest) ? json_decode($manifest, true) : [];

            $this->manifest = is_array($manifest) ? $manifest : [];
        }

        if (isset($this->manifest[$path])) {
            return $this->manifest[$path];
        }

        return $_ENV['PUBLIC_ASSETS_PATH'] . $path;
    }

    /**
     * @param array<string, string> $params
     */
    public function path(string $name, array $params = []): string
    {
        return $this->router->generate($name, $params);
    }
}
