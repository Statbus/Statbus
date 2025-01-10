<?php

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\RouteParserInterface;
use Slim\Routing\RouteContext;

return function (App $app) {
    $app->get("/", \App\Controller\Home\HomeController::class)->setName("home");
    $app->get("/ping", function (Request $request, Response $response) {
        $response->getBody()->write(json_encode("pong"));
        return $response->withHeader('Content-Type', 'application/json');
    });
    $app->get("/logout", \App\Controller\Auth\LogoutController::class)->setName("logout");

    $app->get("/changelog", \App\Controller\Home\MarkdownController::class)->setName("changelog")->setArgument('file', 'changelog.md')->setArgument('title', 'Changelog');

    $app->get("/privacy", \App\Controller\Home\MarkdownController::class)->setName("privacy")->setArgument('file', 'privacy-policy.md')->setArgument('title', 'Privacy Policy');

    $app->get("/warning", \App\Controller\Home\MarkdownController::class)->setName("warning")->setArgument('file', 'content-warning.md')->setArgument('title', 'Content Warning');

    $app->post("/search", \App\Controller\Home\GlobalSearchController::class)->setName('search');

    $settings = $app->getContainer()->get('settings')['app'];
    $app->redirect('/discord', $settings['discord'], 301)->setName('discord');

    // $app->get("/r/{round:[0-9]+}", function (Request $request, Response $response, $args) {
    //     $app = $this->get(App::class);
    //     $routeParser = $app->getRouteCollector()->getRouteParser();
    //     $parser = $app->getRouteCollector()->getRouteParser();
    //     $url = $routeParser->urlFor('round.single', ['id' => $args['round']]);
    //     return $response->withHeader('Location', $url);
    // });

    //Authentication Controllers
    $app->group("/auth", function (RouteCollectorProxy $app) {
        //Via Discord
        $app->get("/discord", \App\Controller\Auth\StartDiscordAuthenticationController::class)->setName("auth.discord");
        $app->get("/discord/check", \App\Controller\Auth\FinishDiscordAuthenticationController::class)->setName("auth.discord.check");

        //Via forums
        $app->get("/tgforum", \App\Controller\Auth\StartForumAuthenticationController::class)->setName("auth.forum");
        $app->get("/tgforum/success", \App\Controller\Auth\FinishForumAuthenticationController::class)->setName("auth.forum.check");
    });

    //Bans
    $app->group("/bans", function (RouteCollectorProxy $app) {
        $app->get("", \App\Controller\Ban\UserBanController::class)->setName("user.bans");
        $app->get("/{id:[0-9]+}", \App\Controller\Ban\ViewBanController::class)->setName("ban.view");
    });

    //Notes
    $app->group("/notes", function (RouteCollectorProxy $app) {
        $app->get("[/page/{page:[0-9]+}]", \App\Controller\Note\UserNotesController::class)->setName("user.notes");
        $app->get("/{id:[0-9]+}", \App\Controller\Note\UserViewNoteController::class)->setName("user.note");
    });

    //Players
    $app->group("/player", function (RouteCollectorProxy $app) {
        //TODO: Apply to all other ckey routes
        $app->get("/{ckey:[\S\s]+}", \App\Controller\Player\ViewPlayerController::class)->setName("player");
    });

    //Rounds
    $app->group("/rounds", function (RouteCollectorProxy $app) {
        $app->get("[/page/{page:[0-9]+}]", \App\Controller\Round\RoundIndexController::class)->setName("rounds");
        $app->get("/myrounds[/page/{page:[0-9]+}]", \App\Controller\Round\RoundsForPlayerController::class)->setName("rounds.player")->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
            $request = $request->withAttribute('user', true);
            $response = $handler->handle($request);
            return $response;
        });
        $app->get("/player/{ckey}[/page/{page:[0-9]+}]", \App\Controller\Round\RoundsForCkeyController::class)->setName("rounds.ckey");
        $app->get("/{id:[0-9]+}", \App\Controller\Round\RoundViewController::class)->setName("round.single");
        $app->get("/{id:[0-9]+}/timeline", \App\Controller\Round\RoundTimelineController::class)->setName("round.timeline");
        $app->get("/{id:[0-9]+}/logs", \App\Controller\Round\RoundLogsRedirect::class)->setName("round.logs.redirect");
        $app->get("/{id:[0-9]+}/{stat:[a-z_]+}", \App\Controller\Round\RoundStatController::class)->setName("round.stat");
    });

    //Info pages
    $app->group("/info", function (RouteCollectorProxy $app) {
        $app->get("/admins", \App\Controller\Info\AdminRosterController::class)->setName("admins");
        $app->get("/adminlogs[/page/{page:[0-9]+}]", \App\Controller\Info\AdminLogController::class)->setName("adminlogs");
        $app->get("/policy[/{server:[a-z]+}]", \App\Controller\Info\PolicyViewerController::class)->setName("policy");
    });


    //Ticket pages
    $app->group("/tickets", function (RouteCollectorProxy $app) {
        $app->get("[/page/{page:[0-9]+}]", \App\Controller\Tickets\TicketListingController::class)->setName("user.tickets");
        $app->get("/{round:[0-9]+}/{ticket:[0-9]+}", \App\Controller\Tickets\TicketViewerController::class)->setName("user.ticket");
    });

    //Library
    $app->group("/library", function (RouteCollectorProxy $app) {
        $app->map(['GET','POST'], "[/page/{page:[0-9]+}]", \App\Controller\Library\LibraryIndexController::class)->setName("library");
        $app->map(['GET','POST'], "/{book:[0-9]+}", \App\Controller\Library\LibraryBookController::class)->setName("library.book");
        $app->get("/dupes", \App\Controller\Library\LibraryDuplicateController::class)->setName("library.dupes");
    })->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $request = $request->withAttribute('authenticated', true);
        $response = $handler->handle($request);
        return $response;
    });

    //Jobs
    $app->group("/jobs", function (RouteCollectorProxy $app) {
        $app->get("", \App\Controller\Jobs\JobListController::class)->setName("jobs");
        $app->get("/{job}", \App\Controller\Jobs\JobViewController::class)->setName("job.single");
    });

    //TGDB
    $app->group("/tgdb", function (RouteCollectorProxy $app) {
        $app->get("", \App\Controller\TGDB\TGDBController::class)->setName("tgdb");

        //Feedback link
        $app->map(['GET','POST'], "/feedback", \App\Controller\TGDB\FeedbackController::class)->setName("tgdb.feedback");

        //Tools
        $app->get("/tools/newplayers", \App\Controller\TGDB\Player\TGDBNewPlayerController::class)->setName("tgdb.newplayers");

        //TLP Guide
        $app->get("/tlp", \App\Controller\Home\MarkdownController::class)->setName("tgdb.tlp")->setArgument('file', 'tlp_guide.md')->setArgument('title', 'Guide to TLP');

        //TGDB Tickets
        $app->map(['POST','GET'], "/tickets/live", \App\Controller\TGDB\Tickets\TGDBTicketFeedController::class)->setName("tgdb.tickets.live");

        $app->get("/tickets[/page/{page:[0-9]+}]", \App\Controller\TGDB\Tickets\TGDBTicketListingController::class)->setName("tgdb.tickets");

        $app->get("/tickets/{round:[0-9]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Tickets\TGDBTicketRoundListingController::class)->setName("tgdb.tickets.round");

        $app->get("/tickets/{round:[0-9]+}/{ticket:[0-9]+}", \App\Controller\TGDB\Tickets\TGDBTicketViewerController::class)->setName("tgdb.ticket");

        $app->get("/tickets/player/{ckey:[a-z0-9@]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Tickets\TGDBTicketsForPlayerListingController::class)->setName("tgdb.ticket.player");

        //TGDB Players
        $app->get("/player/{ckey:[a-z0-9@]+}", \App\Controller\TGDB\Player\TGDBPlayerViewController::class)->setName("tgdb.player");

        $app->get("/player/{ckey:[a-z0-9@]+}/discord", \App\Controller\TGDB\Player\TGDBPlayerDiscordController::class)->setName('tgdb.player.discord');

        //TGDB Bans
        $app->get("/bans[/page/{page:[0-9]+}]", \App\Controller\TGDB\Ban\TGDBBansListingController::class)->setName("tgdb.bans");

        $app->get("/bans/{ckey:[a-z0-9@]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Ban\TGDBBansByCkeyController::class)->setName("tgdb.bans.player");

        $app->get("/ban/{id:[0-9]+}", \App\Controller\TGDB\Ban\TGDBBanViewController::class)->setName("tgdb.ban.view");

        //Notes
        $app->get("/notes[/page/{page:[0-9]+}]", \App\Controller\TGDB\Note\TGDBNotesListingController::class)->setName("tgdb.notes");

        $app->get("/notes/edits[/page/{page:[0-9]+}]", \App\Controller\TGDB\Note\TGDBEditedNotesListingController::class)->setName("tgdb.notes.edited");

        $app->get("/notes/watchlist[/page/{page:[0-9]+}]", \App\Controller\TGDB\Note\TGDBWatchlistListingController::class)->setName("tgdb.watchlist");

        $app->get("/notes/{ckey:[a-z0-9@]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Note\TGDBNotesController::class)->setName("tgdb.notes.player");

        $app->get("/notes/author/{ckey:[a-z0-9@]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Note\TGDBNotesByAuthorController::class)->setName("tgdb.notes.author");

        $app->get("/note/{id:[0-9]+}", \App\Controller\TGDB\Note\TGDBViewNoteController::class)->setName("tgdb.note");

        //Library
        $app->get("/library/author/{ckey:[a-z0-9@]+}[/page/{page:[0-9]+}]", \App\Controller\TGDB\Library\TGDBLibraryByAuthorController::class)->setName("tgdb.library.author");

    })->add(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $request = $request->withAttribute('require', 'ADMIN');
        $response = $handler->handle($request);
        return $response;
    });

};
