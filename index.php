<?php
require("method_builder.php");
require("token.php");
require("message.php");

if(!isset($_REQUEST)){
    return;
}

$data = json_decode(file_get_contents('php://input'));


switch ($data->type){

    case 'confirmation':
        echo VK_CONFIRMATION_CODE;
        break;

    case 'message_new':
        $message = new Message;
        $method_builder = new Method_builder;
        $text = $data->object->body;
        $id = $data->object->user_id;
        $group = $method_builder->checkGroup($id);
        $request = "";
        if($group == -1){
            $message->sendMessage($id, "Напишите свою группу: ");
        }else{
            $group_href = $method_builder->getGroup($group);
            $flag = $method_builder->currentweektype();
            $request = $method_builder->CheckBody($text, $group_href, $flag);
        }
        $message->sendMessage($id, $request);
        break;

    case 'message_reply':
        echo "ok";
        break;

    case 'last_message_id':

        break;

    default:
        echo 'ok';
        break;
}
?>