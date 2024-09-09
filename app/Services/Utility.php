<?php
namespace App\Services;

use App\Libraries\WebApiResponse;

class Utility{
    public static function textTransformToClientInfo($problemAndGoal, $text){
        try{
            if(empty($problemAndGoal['meetingTranscript'])){
                return WebApiResponse::error(500, $errors = [], 'Problem and Goal not found.');
            }
            $input = [
                "CLIENT-EMAIL" => $problemAndGoal->meetingTranscript->clientEmail,
                "CLIENT-COMPANY-NAME" => $problemAndGoal->meetingTranscript->company,
                "CLIENT-PHONE" => $problemAndGoal->meetingTranscript->clientPhone,
            ];
            $title = strip_tags($text);
            foreach ($input as $key => $value) {
                $placeholder = "{" . $key . "}";
                $title = str_replace($placeholder, $value, $title);
            }
            return $title;
        }catch(\Exception $exception) {
            return $text;
        }
    }

}
