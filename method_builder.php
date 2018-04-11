<?php
require("simple_html_dom.php");
require("constants.php");
require("query_builder.php");

Class Method_builder{

    public function extract_true_tr_from_garbage($s) {
        $tr_starting_pos = strpos($s, "<tr>");
        $tr_closing_pos = strpos($s, "</TR>");
        return substr($s, $tr_starting_pos + 4, $tr_closing_pos - $tr_starting_pos - 5);
    }

    public function convertDay($day){
        switch(mb_strtolower($day)){
            case 'понедельник':
                return 'Пнд';
                break;
            case 'вторник':
                return 'Втр';
                break;
            case 'среда':
                return 'Срд';
                break;
            case 'четверг':
                return 'Чтв';
                break;
            case 'пятница':
                return 'Птн';
                break;
            case 'суббота':
                return 'Сбт';
                break;
            case 'воскресенье':
                return 'Воскресенье';
                break;
            default:
                return 0;
        }
    }

    public function convertDayEng($day){
        switch(mb_strtolower($day)){
            case 'monday':
                return 'Пнд';
                break;
            case 'tuesday':
                return 'Втр';
                break;
            case 'wednesday':
                return 'Срд';
                break;
            case 'thursday':
                return 'Чтв';
                break;
            case 'friday':
                return 'Птн';
                break;
            case 'saturday':
                return 'Сбт';
                break;
            case 'sunday':
                return 'Воскресенье';
                break;
            default:
                return 0;
        }
    }

    public function today() {
        $today = date('l');
        return $this->convertDayEng($today);
    }

    public function divideStr($str){
        return str_replace("td", "td>\n<td", $str);
    }

    public function currentweektype(){
        $today = date_create(date('Y-m-d'));
        $studyBeginning = date_create('2018-02-05');
        $interval = date_diff($today, $studyBeginning);

        $days = $interval->days;
        $days = intdiv($days, 7);

        if ($days % 2 == 0){
            return 1;
        }
        else{
            return 2;
        }
    }

    public function CheckBody($text, $group_href, $flag){
        switch (mb_strtolower($text)){
            case "на день":
            case "на сегодня":
                $day = $this->today();
                if ($day == "Воскресенье"){
                    $answer = "Совет дня\nСегодня выходной!)";
                } else {
                    $answer = "Сейчас ".$flag." неделя\nСегодня:\n".$this->printSchedule_day($day, $group_href, $flag);
                }
                return $answer;

            case "на завтра":
                $day = strtotime("+1 day");
                return  "Завтра\n".$this->printSchedule_day($this->convertDayEng(date('l',$day)), $group_href, $flag);

            case "на послезавтра":
                $day = strtotime("+2 day");
                return  "Послезавтра\n".$this->printSchedule_day($this->convertDayEng(date('l',$day)), $group_href, $flag);

            case "на неделю":
                return  "Расписание ".$flag." недели:\n".$this->printSchedule_week($group_href, $flag);

            case "все":
                return  "В разработке";

            default:
                return "Отведенное место для => Напиши свою группу: ";
        }
    }

    public function getGroup ($group){
        $ch = curl_init('http://www.ulstu.ru/schedule/students/raspisan.htm');

        // Указываем, что результат должен записаться в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем CURL запрос
        $data = curl_exec($ch);
        $group_href = "";
        // Передаём данные, полученные с помощью CURL в SimpleHtmlDom функцию
        $html = str_get_html($data);
        foreach($html->find('a') as $element){
            if ($element->plaintext == $group) {
                $group_href = $element->href;
                break;
            }
        }
        $html->clear();
        unset($html);
        return $group_href;
    }

    public function setGroup($id, $group){
        $query_builder = new Query_builder();
        $array = array(
            'user_group' =>  $group
    );
        $query_builder->update("user", $array, "user_id = " .$id);
        $result = $query_builder->select("user", null, 'user_id = "' . $id . '" LIMIT 1');
        return $result[0][2];
    }

    public function checkGroup($id){
        $query_builder = new Query_builder();
        $array = array(
            'user_id' => $id,
            'user_group' =>  ""
        );
        $result = $query_builder->select("user", null, 'user_id = "' . $id . '" LIMIT 1');
        if($result){
            return $result[0][2];
        }else{
            $query_builder->insert("users", $array);
            return -1;
        }
    }

    public function printSchedule_day($day, $group_href, $flag){
        $target = 'http://www.ulstu.ru/schedule/students/' . $group_href;
        $ch = curl_init($target);
        // Указываем, что результат должен записаться в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем CURL запрос
        $data = curl_exec($ch);

        // Передаём данные, полученные с помощью CURL в SimpleHtmlDom функцию
        $table = str_get_html($data);
        foreach($table->find('tr') as $element){
            $table_day = strip_tags($element->find('td p', 0));
            if ($day === $table_day) {
                $pair = $this->extract_true_tr_from_garbage($element);
                if ($flag == 1) {
                    $pair=$this->divideStr($pair);
                    $table->clear();
                    unset($table);
                    return strip_tags($pair)."\n\n";
                    break;
                } else {
                    $flag--;
                }
            }
        }
        return 0;
    }

    public function printSchedule_week($group_href, $flag){
        return $this->printSchedule_day("Пнд", $group_href, $flag).$this->printSchedule_day("Втр", $group_href, $flag).$this->printSchedule_day("Срд", $group_href, $flag).$this->printSchedule_day("Чтв", $group_href, $flag).$this->printSchedule_day("Птн", $group_href, $flag).$this->printSchedule_day("Сбт", $group_href, $flag);
    }

    public function printAllSchedule($group_href){
        $target = 'http://www.ulstu.ru/schedule/students/' . $group_href;
        $ch = curl_init($target);
        // Указываем, что результат должен записаться в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем CURL запрос
        $data = curl_exec($ch);

        // Передаём данные, полученные с помощью CURL в SimpleHtmlDom функцию
        $table = str_get_html($data);
        $passFirstRow = 0;
        $result="";
        foreach($table->find('tr') as $element){
            if($passFirstRow >= 2) {
                $table_day = strip_tags($element->find('td p', 0));
                $pair = $this->extract_true_tr_from_garbage($element);
                $result.=$pair;
                $result=strip_tags($this->divideStr($result));
            }
            $passFirstRow++;
        }
        $table->clear();
        unset($table);
        return  $result;
    }

}

?>