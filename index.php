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
        if(mb_strtolower(mb_substr($text, 0, 10, 'UTF-8')) == "моя группа"){
            if($method_builder->setGroup($id, mb_substr($text, 11,strlen($text) - 11, 'UTF-8')) == 1) {
                $message->sendMessage($id, "&#9989;Ваша группа успешно установлена");
            } else {
                $message->sendMessage($id, "&#9940;Ваша группа не найдена.\nЧтобы установить группу используйте команду:\n Моя группа (название группы)");
            }
            break;
        }
        if($group == -1){
            $message->sendMessage($id, "&#8252;Вам необходимо указать свою группу, чтобы продолжить.\nДля этого используйте команду Моя группа (название группы)");
            break;
        }else{
            $group_href = $method_builder->getGroup($id);
            
          if(mb_strtolower($text) == "сайт" || mb_strtolower($text == "site")) {
              $message->sendMessage($id, $group_href);
              break;
          } else {
              $flag = $method_builder->currentWeekType();
              $request = $method_builder->CheckBody($id, $text, $group, $flag);
              $message->sendMessage($id, $request);
            }
        }
        break;
}
?>