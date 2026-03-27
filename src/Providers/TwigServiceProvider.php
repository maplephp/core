<?php

declare(strict_types=1);

namespace MaplePHP\Core\Providers;

use MaplePHP\Core\App;
use MaplePHP\Core\Support\ServiceProvider;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigServiceProvider extends ServiceProvider
{
    public function register(ContainerInterface $container): void
    {
        $container->set(Environment::class, function () {
            $loader = new FilesystemLoader(App::get()->dir()->resources());

            $twig = new Environment($loader, [
                'cache' => App::get()->isProd()
                    ? App::get()->dir()->cache() . '/twig'
                    : false,
                'debug' => App::get()->isDev(),
                'auto_reload' => true,
            ]);

            return $twig;
        });
    }
}
