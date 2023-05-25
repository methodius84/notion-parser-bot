<?php

namespace App\Controller;

use App\Service\TelegramCommandHandlerService;
use App\TelegramCommands\Command\ParseNotion;
use App\TelegramCommands\Command\ParsePreviousDay;
use App\TelegramCommands\Command\Start;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;
use SergiX44\Nutgram\RunningMode\Webhook;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

class TelegramController extends AbstractController
{
    private string $botApiKey;
    private string $botName;

    public function __construct()
    {
        $this->botApiKey = $_ENV['TELEGRAM_TOKEN'];
        $this->botName = 'MethodiusNotionBot';
    }

    #[Route('/handle', name: 'handle_command')]
    public function handle(){
        try {
            $bot = new Nutgram($this->botApiKey);
            $bot->setRunningMode(Webhook::class);
            $bot->registerCommand(Start::class);
            $bot->registerCommand(ParseNotion::class);
            $bot->registerCommand(ParsePreviousDay::class);
            $bot->run();
        }
        catch (ContainerExceptionInterface){

        }


        return new Response();
    }
}
