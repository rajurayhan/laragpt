<?php
namespace App\Services;

use ErrorException;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClickUpService
{
    public static function getClickUpTasksByListId($listId){
        $baseUrl = "https://api.clickup.com/api/v2/list/";
        $listTaskUrl = $baseUrl.$listId.'/task?';

        $taskQuery = array(
            "subtasks" => "1",
        );

        $listTask = Http::withHeaders([
            "Authorization" => env('CLICKUP_API_KEY', 'pk_38254709_31XI582SYM6HN73D7ZZNAF17B51KI1Y2'),
            "Content-Type" => "application/json"
        ])->get($listTaskUrl.http_build_query($taskQuery));

        if(isset($listTask['err'])){
            throw new ErrorException($listTask['err'], 1);

        }
        return $listTask['tasks'];
    }
}
