<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Libraries\WebApiResponse;
use App\Models\ChatGptThreadUsing;

/**
 * @authenticated
 */

 class ThreadUsingController extends Controller{

    /**
     * Get chatGpt thread using data
     *
     * @group ChatGpt Thread Using
     *
     * @urlParam threadId int required thread id of the ChatGPT thread
     */

    public function getThread($threadId){
        $chatGptThread = ChatGptThreadUsing::with(['userInfo'])->where('threadId',$threadId)->first();
        if (!$chatGptThread) {
            return WebApiResponse::error(404, $errors = [], 'The thread is not active now.');
        }
        return response()->json($chatGptThread, 201);

    }
}
