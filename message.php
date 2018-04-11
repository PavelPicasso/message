<?php
require("token.php");

class Message {

    public function sendMessage($id,$request){
        $request_params = array(
            'user_id' => $id,
            'message' =>  $request,
            'access_token' => VK_TOKEN,
            'v' => '5,69'
        );
        file_get_contents('https://api.vk.com/method/messages.send?' . http_build_query($request_params));
        echo 'ok';
    }

}