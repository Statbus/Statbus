<?php

use App\Controller;
use App\Domain\User\Repository\UserRepository;
use App\Extension\Twig\EnumExtension;
use App\Extension\Twig\WebpackAssetLoader;
use App\Factory\LoggerFactory;
use App\Handler\DefaultErrorHandler;
use App\Repository\Repository;
use Cake\Database\Connection;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\MarkdownConverter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Nyholm\Psr7\Factory\Psr17Factory;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\Factory;
use ParagonIE\EasyDB\EasyDBCache;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\Factory\AppFactory;
use Slim\Interfaces\RouteParserInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Twig\Extra\Markdown\MarkdownRuntime;
use Twig\Extra\String\StringExtension;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

return [

    //Settings
    'settings' => function () {
        return require __DIR__ . "/settings.php";
    },

    //Application
    App::class => function (ContainerInterface $container) {
        $app = AppFactory::createFromContainer($container);
        $settings = $container->get('settings')['error'];

        // Register routes
        (require __DIR__ . '/routes.php')($app);

        // Register middleware
        (require __DIR__ . '/middleware.php')($app);

        return $app;
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
    },

    //Route parsing
    RouteParserInterface::class => function (ContainerInterface $container) {
        return $container
        ->get(App::class)
        ->getRouteCollector()
        ->getRouteParser();
    },

    //TwigMiddleware
    TwigMiddleware::class => function (ContainerInterface $container) {
        return TwigMiddleware::createFromContainer(
            $container->get(App::class),
            Twig::class
        );
    },

    //Twig
    Twig::class => function (ContainerInterface $container) {
        $session = $container->get(Session::class);
        $config = (array) $container->get("settings");
        $settings = $config["twig"];
        $options = $settings["options"];
        $options["cache"] = $options["cache_enabled"]
        ? $options["cache_path"]
        : false;

        // var_dump($settings, $options);

        $twig = Twig::create($settings["paths"], $options);

        $loader = $twig->getLoader();
        $publicPath = (string) $config["public"];
        if ($loader instanceof FilesystemLoader) {
            $loader->addPath($publicPath, "public");
        }

        $twig->getEnvironment()->addGlobal("debug", $config["debug"]);
        $twig->getEnvironment()->addGlobal("app", $config["app"]);
        $twig->getEnvironment()->addGlobal("flash", $session->getFlashBag()->all());

        $twig->addExtension(new \Twig\Extension\DebugExtension());
        $twig->addExtension(new WebpackAssetLoader($options));
        $twig->addExtension(new EnumExtension($options));
        $twig->addExtension(new StringExtension());
        $twig->addRuntimeLoader(new class () implements RuntimeLoaderInterface {
            public function load($class)
            {
                $config = [
                    'default_attributes' => [
                        Table::class => [
                            'class' => 'table table-bordered',
                        ],
                    ],
                ];
                if (MarkdownRuntime::class === $class) {
                    $environment = new Environment($config);
                    $environment->addExtension(new CommonMarkCoreExtension());
                    $environment->addExtension(new DefaultAttributesExtension());
                    $environment->addExtension(new GithubFlavoredMarkdownExtension());
                    return new MarkdownConverter($environment);
                }
            }
        });
        $twig->addExtension(new \Twig\Extra\Markdown\MarkdownExtension());
        $twig->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setDateFormat('Y-m-d H:i:s', '%a minutes');
        $twig->getEnvironment()->getExtension(\Twig\Extension\CoreExtension::class)->setTimezone('UTC');
        return $twig;
    },

    //Session
    Session::class => function (ContainerInterface $container) {
        $settings = $container->get("settings")["session"];
        if (PHP_SAPI === "cli") {
            return new Session(new MockArraySessionStorage());
        } else {
            return new Session(new NativeSessionStorage($settings));
        }
    },

    Connection::class => function (ContainerInterface $container) {
        return new Connection($container->get('settings')['db'], $container->get(EasyDB::class));
    },

    Repository::class => function (ContainerInterface $container) {
        return new Repository($container->get(Connection::class), $container->get(EasyDB::class));
    },

    EasyDB::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        $dsn = sprintf(
            "mysql:host=%s:%s;dbname=%s",
            $settings['host'],
            $settings['port'],
            $settings['database']
        );
        try {
            return EasyDBCache::fromEasyDB(Factory::create($dsn, $settings['username'], $settings['password'], $settings['flags']));
        } catch (Exception $e) {
            die("The /tg/station database is not available. This should be a temporary error.");
        }
    },

    LoggerFactory::class => function (ContainerInterface $container) {
        return new LoggerFactory($container->get('settings')['logger']);
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['error'];
        $app = $container->get(App::class);
        $formatter = new JsonFormatter();
        $stream = new StreamHandler('php://stderr', Logger::DEBUG);
        $stream->setFormatter($formatter);
        $logger = $log = new Logger('stderr');
        $log->pushHandler($stream);


        $errorMiddleware = new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details'],
            $logger
        );

        $errorMiddleware->setDefaultErrorHandler($container->get(DefaultErrorHandler::class));

        return $errorMiddleware;
    },

];
