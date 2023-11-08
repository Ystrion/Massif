<?php

declare(strict_types=1);

namespace Application;

use Application\Controllers\DefaultController;
use Application\Middleware\ResponseEmitterMiddleware;
use Application\Middleware\SessionMiddleware;
use Application\Utils\Settings;
use Application\Utils\Twig\UrlExtension;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Dotenv\Dotenv;
use Nyholm\Psr7Server\ServerRequestCreator;
use Nyholm\Psr7Server\ServerRequestCreatorInterface;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Ystrion\MiddlewareDispatcher\StackMiddlewareDispatcher;
use Ystrion\ViaRouter\Routes;
use Ystrion\ViaRouter\ViaRouter;

class Kernel
{
    protected const ROOT_PATH = __DIR__ . '/..';
    protected const RESOURCES_PATH = self::ROOT_PATH . '/resources';
    protected const STORAGE_PATH = self::ROOT_PATH . '/storage';

    protected Container $container;

    public function initSettings(): void
    {
        Dotenv::createImmutable(self::ROOT_PATH)->load();
        Settings::init($this->settings());
    }

    public function initContainer(): Container
    {
        $containerBuilder = new ContainerBuilder();

        $containerBuilder->useAttributes(true);
        $containerBuilder->addDefinitions($this->container());

        if (Settings::isProd()) {
            $containerBuilder->enableCompilation(self::STORAGE_PATH . '/container');

            if (extension_loaded('apcu')) {
                $containerBuilder->enableDefinitionCache();
            }

            $containerBuilder->writeProxiesToFile(true, self::STORAGE_PATH . '/container');
        }

        return $this->container = $containerBuilder->build();
    }

    public function launch(): void
    {
        $this->container->get(StackMiddlewareDispatcher::class);
    }

    /**
     * @return array<string, mixed>
     */
    protected function settings(): array
    {
        return [
            'env' => $_SERVER['ENV'],
            'cookies' => [
                'prefix' => '__Host-',
                'path' => '/',
                'domain' => '',
                'secure' => $_SERVER['COOKIES_SECURE'] === 'true',
                'httpOnly' => true,
                'sameSite' => 'Strict'
            ],
            'database' => [
                'driver' => $_SERVER['DATABASE_DRIVER'],
                'host' => $_SERVER['DATABASE_HOST'],
                'user' => $_SERVER['DATABASE_USER'],
                'password' => $_SERVER['DATABASE_PASSWORD'],
                'dbname' => $_SERVER['DATABASE_DBNAME'],
                'charset' => $_SERVER['DATABASE_CHARSET']
            ],
            'migrations' => [
                'migrations_paths' => [
                   'Application\Database\Migrations' => __DIR__ . '/Database/Migrations'
                ]
            ],
            'sessions' => [
                'name' => '__Host-SESSIONID',
                'savePath' => self::STORAGE_PATH . '/sessions'
            ],
            'paths' => [
                'root' => self::ROOT_PATH,
                'resources' => self::RESOURCES_PATH,
                'storage' => self::STORAGE_PATH
            ],
            'modules' => $this->modules(),
            'controllers' => $this->controllers()
        ];
    }

    /**
     * @return array<class-string, mixed>
     */
    protected function container(): array
    {
        return [
            ResponseFactoryInterface::class => \DI\get(Psr17Factory::class),
            ServerRequestFactoryInterface::class => \DI\get(Psr17Factory::class),
            StreamFactoryInterface::class => \DI\get(Psr17Factory::class),
            UploadedFileFactoryInterface::class => \DI\get(Psr17Factory::class),
            UriFactoryInterface::class => \DI\get(Psr17Factory::class),
            ServerRequestCreatorInterface::class => \DI\get(ServerRequestCreator::class),
            EntityManagerInterface::class => function () {
                $config = ORMSetup::createAttributeMetadataConfiguration([
                    __DIR__ . '/Database/Entities'
                ], Settings::isDev());

                /** @phpstan-ignore-next-line */
                $connection = DriverManager::getConnection([
                    'driver' => Settings::getString('database.driver'),
                    'host' => Settings::getString('database.host'),
                    'user' => Settings::getString('database.user'),
                    'password' => Settings::getString('database.password'),
                    'dbname' => Settings::getString('database.dbname'),
                    'charset' => Settings::getString('database.charset')
                ], $config);

                return new EntityManager($connection, $config);
            },
            Environment::class => function (UrlExtension $urlExtension): Environment {
                $loader = new FilesystemLoader(Settings::getString('paths.resources') . '/templates');

                $twig = new Environment($loader, [
                    'debug' => Settings::isDev(),
                    'cache' => Settings::isProd() ? Settings::getString('paths.storage') . '/twig' : false
                ]);

                if (Settings::isDev()) {
                    $twig->addExtension(new DebugExtension());
                }

                $twig->addExtension($urlExtension);

                return $twig;
            },
            StackMiddlewareDispatcher::class => function (
                ResponseFactoryInterface $responseFactory,
                Container $container,
                ServerRequestCreatorInterface $requestCreator
            ): void {
                $middlewareDispatcher = new StackMiddlewareDispatcher($responseFactory, $container);

                /** @var class-string[] $modules */
                $modules = Settings::get('modules');

                $middlewareDispatcher->set($modules);
                $middlewareDispatcher->handle($requestCreator->fromGlobals());
            },
            ViaRouter::class => function (Container $container): ViaRouter {
                if (Settings::isProd() && extension_loaded('apcu') && apcu_exists('viarouter.routes')) {
                    $routes = apcu_fetch('viarouter.routes');
                } else {
                    $routes = new Routes();

                    /** @var class-string[] $controllers */
                    $controllers = Settings::getArray('controllers');

                    foreach ($controllers as $controller) {
                        $routes->addController($controller);
                    }

                    if (Settings::isProd() && extension_loaded('apcu')) {
                        $routes->compile();

                        apcu_store('viarouter.routes', $routes);
                    }
                }

                /** @phpstan-ignore-next-line */
                return new ViaRouter($routes, container: $container);
            }
        ];
    }

    /**
     * @return class-string[]
     */
    protected function modules(): array
    {
        return [
            SessionMiddleware::class,
            ViaRouter::class,
            ResponseEmitterMiddleware::class
        ];
    }

    /**
     * @return class-string[]
     */
    protected function controllers(): array
    {
        return [
            DefaultController::class
        ];
    }
}
