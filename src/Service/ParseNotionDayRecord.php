<?php

namespace App\Service;

use Carbon\Carbon;
use Monolog\Logger;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;

class ParseNotionDayRecord
{
    public function getPages(Carbon $date): string
    {
        // my space id
        $mainPage = $_ENV['NOTION_SPACE_ID'];

        $data = $this->getBlockData($mainPage);

        foreach ($data->results as $year){
            if($year->has_children === true){
                $childPage = $year->child_page->title;
                if ((int)$childPage === $date->year){
                    $pageId = $year->id;
                    break;
                }
            }
        }

        $data = $this->getBlockData($pageId)->results;
        foreach ($data as $page){
            if ($page->has_children === true){
                $childPage = $page->child_page->title;
                $sprint = (int)str_replace('Спринт ','', $childPage);
                if($sprint === $date->week){
                    $pageId = $page->id;
                    break;
                }
            }
        }

        $data = $this->getBlockData($pageId);

        $plan = '';

        $results = $data->results;


//        echo '<pre>';
//        var_dump($results);
//        echo '</pre>';
        // week plan
        foreach ($results as $block){

            if ($block->type === 'heading_3')
            {
                $plan .= '### '.$block->heading_3->rich_text[0]->text->content."\n\n";
            }
            if ($block->type === 'to_do'){
                $plan .= $this->parseToDo($block->to_do);
            }
            if ($block->type === 'divider'){
                $plan .= "\n";
                break;
            }

            array_shift($results);
        }

        // parse days
        foreach ($results as $block){
            if ($block->type === 'toggle'){
                $plan .= $this->parseToggleBlock($block, $date);
            }
        }
        return $plan;
    }

    private function getBlockData(string $pageId, string $resource = 'blocks', string $method = 'GET')
    {
        $client = HttpClient::create([
            'base_uri' => 'https://api.notion.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer '.$_ENV['NOTION_TOKEN'],
                'Notion-Version' => '2022-06-28',
                'accept' => 'application/json',
            ],
        ]);

        $response = $client->request($method, $resource.'/'.$pageId.'/children');

        return json_decode($response->getContent());
    }

    private function parseToDo($point): string
    {
        $string = '[';
        if ($point->checked === true){
            $string .= 'x';
        }
        $string .= '] ';
        foreach ($point->rich_text as $text){
            $string .= $text->text->content;
        }
        $string .= "\n";
        return $string;
    }

    private function parseToggleBlock($toggle, Carbon $date){

        $result = '';

        $parsed = explode('.',$toggle->toggle->rich_text[0]->plain_text);
        $toggleData = Carbon::createFromDate(2023, $parsed[1], $parsed[0], 'Europe/Moscow');


        if ($toggleData->isSameDay($date) || $toggleData->day === $date->day + 1){
            $data = $this->getBlockData($toggle->id)->results;
            foreach ($data as $todo){
                if ($todo->type === 'paragraph'){

                    // TODO сделать обнову План -> Факт
//                    if ($toggleData->isSameDay($currentDate)){
//                        $todo = $this->updateFactBlock($todo->id, "Факт ".$toggleData->day.'.'.$toggleData->month.':');
//                        dd($todo);
//                    }
                    $result .= $todo->paragraph->rich_text[0]->plain_text."\n\n";
                    continue;
                }
                $result .= $this->parseToDo($todo->to_do);
            }

            return $result . "\n";
        }
        else return $result;
    }
}