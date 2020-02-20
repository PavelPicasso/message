<?php
require("simple_html_dom.php");
require("constants.php");
require("query_builder.php");
require("LImageHandler.php");

Class Method_builder{

    public function extractTrueTrFromGarbage($s) {
        $tr_starting_pos = strpos($s, "<tr>");
        $tr_closing_pos = strpos($s, "</TR>");
        return substr($s, $tr_starting_pos + 4, $tr_closing_pos - $tr_starting_pos - 5);
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
  
    public function removeHTMLTagsWithNoContent($htmlBlob) {
    $pattern = "#<p>(\s|&nbsp;|</?\s?br\s?/?>)*</?p>#";
    if (preg_match($pattern, $htmlBlob) == 1) {
        $htmlBlob = preg_replace($pattern, '', $htmlBlob);
        return $this->removeHTMLTagsWithNoContent($htmlBlob);
    } else {
        return '';
    }
}

    public function currentWeekType(){
        $today = date_create(date('Y-m-d'));
        $studyBeginning = date_create('2020-02-03'); // Дата начала учебы
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

    public function CheckBody($id, $text, $group, $flag){
        switch (mb_strtolower($text)){
            case "на день":
            case "на сегодня":
            case "сегодня":
                $day = $this->today();
                if ($day == "Воскресенье"){
                    $answer = "&#9654;Совет дня\n\nСегодня выходной!)";
                } else {
                    $answer = "&#9654;Сейчас ".$flag." неделя\n\n".$this->printScheduleDay($day, $group, $flag);
                }
                return $answer;

            case "на завтра":
            case "завтра":
                $day = strtotime("+1 day");
                 if ($this->convertDayEng(date('l',$day)) == "Воскресенье"){
                    return "Завтра выходной!";
                }
                return  "&#9654;Завтра\n\n".$this->printScheduleDay($this->convertDayEng(date('l',$day)), $group, $flag);

            case "на послезавтра":
            case "послезавтра":
                $day = strtotime("+2 day");
                 if ($this->convertDayEng(date('l',$day)) == "Воскресенье"){
                    return "Послезавтра выходной!";
                }
                return  "&#9654;Послезавтра\n\n".$this->printScheduleDay($this->convertDayEng(date('l',$day)), $group, $flag);
          
            case "на неделю":
            case "неделя":
                 return  "&#9654;Расписание " . $flag . " недели:\n\n".$this->printScheduleWeek($id, $group, $flag);
          
            case "на 1 неделю":
            case "1 неделя":
            case "1":
                    return  "&#9654;Расписание 1 недели:\n\n".$this->printScheduleWeek($id, $group, 1);
            
            case "на 2 неделю":
            case "2 неделя":
            case "2":
                    return  "&#9654;Расписание 2 недели:\n\n".$this->printScheduleWeek($id, $group, 2);

           case "help":
            case "помощь":
            case "команды":
                    return  "&#128204; Текущие команды:\n\n1&#8419;Моя группа Название группы\nУстанавливает текущую группу\n2&#8419;на день/на сегодня/сегодня\nВозвращает пары на текущий день\n3&#8419;на завтра/завтра\nВозвращает пары на завтра\n4&#8419;на послезавтра/послезавтра\nВозвразает пары на послезавтра\n5&#8419;на неделю/неделя\nВозвразает пары на текущую неделю\n6&#8419;на 1 неделю/1 неделя/1\nВозвразает пары на первую неделю\n7&#8419;на 2 неделю/2 неделя/2\nВозвразает пары на вторую неделю";

            default:
                    return "&#9999;Что я могу\n1&#8419;Узнай команды написав: help/помощь/команды\n2&#8419;Возвращать пары на сегодня/завтра/послезавтра\n3&#8419;Возвращать пары на неделю/на 1 неделю/на 2 неделю\n4&#8419;Моя группа (Название группы)\n5&#8419;Возвращать сайт\n6&#8419;&#128293;В разработке&#128293;";
        }
    }
  
    public function getGroup($id){
        $query_builder = new Query_builder();
        $result = $query_builder->select("user", 'href_group', 'user_id = "' . $id . '" LIMIT 1');

        if($result){
            return $result[0][0];
        }else{
            return -1;
        }
    }

    public function setGroup($id, $group){
        $group_href = "";
        $ch = curl_init('https://www.ulstu.ru/schedule/students/part1/raspisan.htm');
        
        // Указываем, что результат должен записаться в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем CURL запрос
        $data = curl_exec($ch);

        // Передаём данные, полученные с помощью CURL в SimpleHtmlDom функцию
        $html = str_get_html($data);

        foreach($html->find('a') as $element){
          $tag_text = strip_tags($element->innertext);
          if (mb_strtolower($tag_text) == mb_strtolower($group)) {
              $group_href = $element->href;
              break;
          }
        }
      
        if(!$group_href) {
          return -1;
        }
      
        $path_schedule = 'https://www.ulstu.ru/schedule/students/part1/' . $group_href;
      
        $query_builder = new Query_builder();
        $array = array(
            'user_group' =>  $group,
            'href_group' =>  $path_schedule
         );
        $query_builder->update("user", $array, "user_id = " .$id);
        
        $html->clear();
        unset($html);
      
        $ch = curl_init($path_schedule);
        // Указываем, что результат должен записаться в переменную
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Выполняем CURL запрос
        $file_schedule = str_get_html(curl_exec($ch));
        $file_schedule = str_replace("charset=windows-1251", "charset=utf-8", $file_schedule);

        mkdir("group/".$this->translit($group));
        file_put_contents('./group/'.$this->translit($group).'/schedule.html', $file_schedule);
        return 1;
    } 
  
    public function checkGroup($id){
        $query_builder = new Query_builder();
        $array = array(
            'user_id' => $id,
            'user_group' =>  ""
        );
        $result = $query_builder->select("user", null, 'user_id = "' . $id . '" LIMIT 1');
        if($result){
            return $result[0][1];
        }else{
            $query_builder->insert("user", $array);
            return -1;
        }
    }
    
    public function entityIdentificationPair($pair) {
    $result = "";
    $search = 0;
    foreach(str_get_html($pair)->find('p') as $item) {
        if($search >= 1) {
            if($item->innertext && trim($item->innertext) != '_') {
                $item->innertext = "\n" . $search . '&#8419;' . $item->innertext;
            } else {
                $item->innertext = '';
                $item = $this->removeHTMLTagsWithNoContent($item);
            }
            $result .= $item;
            $search++;
        } else {
            $result .= "&#128204;" . $item;
            $search++;
        }
    }
    return $result;
}
  
    public function printScheduleDay($day, $group, $flag){
        $target = file_get_contents('./group/'.$this->translit($group).'/schedule.html');

        $table = str_get_html($target);
        foreach($table->find('tr') as $element){
            $table_day = strip_tags($element->find('td p', 0));
            if ($day === $table_day) {
                $pair = $this->extractTrueTrFromGarbage($element);
                
                if ($flag == 1) {
                    $week .= $this->entityIdentificationPair($pair);               

                    $table->clear();
                    unset($table);

                    return strip_tags($week);
                    break;

                } else {
                    $flag--;
                }
            }
        }
        return 0;
    }

  
    public function translit($value) {
	$converter = array(
		'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
		'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
		'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
		'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
		'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
		'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
		'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
 
		'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
		'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
		'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
		'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
		'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
		'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
		'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya',
	);
 
	$value = strtr($value, $converter);
	return $value;
}
  
  public function entityIdentificationPair_def($pair) {
        $result = "";
        foreach(str_get_html($pair)->find('p') as $item) {
            if($search >= 1) {
                if($item->innertext && trim($item->innertext) != '_') {
                    $item->innertext = "\n" . $search . " " . $item->innertext;
                } else {
                    $item->innertext = '';
                    $item = $this->removeHTMLTagsWithNoContent($item);
                }
            }
            $search++;
            $result .= $item;
        }
        return $result;
    }
  
  public function draWeek($table, $start, $finish, $name, $group) {
        $x = 0;
        $y = 0; 
        // Создаем экземпляр класса LImageHandler
        $ih = new LImageHandler;
        // // Подключаем выбранный шрифт текста
        $fontPath = './OpenSans-Bold.ttf';
        // Путь к оригинальному изображению
        $imagePath = './template.jpg';
        // Указываем размер шрифта 
        $fontSize = 16;
        
        // Задаем цвет
        $colorArray = array(255, 255, 255);
        
        $position = array(
            0 => LImageHandler::CORNER_LEFT_TOP,
            1 => LImageHandler::CORNER_CENTER_TOP,
            2 => LImageHandler::CORNER_RIGHT_TOP,
            3 => LImageHandler::CORNER_LEFT_BOTTOM,
            4 => LImageHandler::CORNER_CENTER_BOTTOM,
            5 => LImageHandler::CORNER_RIGHT_BOTTOM,
        );
        $i = 0;
        $passFirstRow = 0;
        foreach($table->find('tr') as $element) {
            
            if($passFirstRow >= $start && $passFirstRow <= $finish) {
                $pair = $this->extractTrueTrFromGarbage($element);

                $week = "\n" . str_replace("<br>", "\n", $this->entityIdentificationPair_def($pair));

                // Загружаем изображение
                if($passFirstRow == $start) {
                    $imgObj = $ih->load($imagePath);
                    $y -= ceil(strlen($week) / 2);
                } else {
                    $imgObj = $ih->load("./group/IVTAPbd-41/" . $name . ".jpg");
                    if($i == 1) {
                        $y = -ceil(strlen($week) / 2);
                    }
                    if($i == 2) {
                        $y = -ceil(strlen($week) / 2);
                    }
                }

                if($passFirstRow == ceil(($start + $finish) / 2)) {
                    $y = 0;
                    $y += 700;
                }
                $imgObj->text(strip_tags($week), $fontPath, $fontSize, $colorArray, $position[$i], $x, $y);
                $i++;
                $imgObj->save("./group/IVTAPbd-41/" . $name . ".jpg");
            }
            $passFirstRow++;
        }
        $url = "http://a323177.mcdir.ru/group/" . $this->translit($group) . "/" . $name . ".jpg";
        return $url;
    }
  
    public function printScheduleWeek($id, $group, $flag) {
          $target = file_get_contents('./group/'.$this->translit($group).'/schedule.html');
          $table = str_get_html($target);
          $query_builder = new Query_builder();
          if($flag == 1) {
              $start = 2;
              $finish = 7;
              $result = $query_builder->select("user", 'first_week', 'user_id = "' . $id . '" LIMIT 1');
          }
         if($flag == 2) {
            $start = 11;
            $finish = 16;
            $result = $query_builder->select("user", 'second_week', 'user_id = "' . $id . '" LIMIT 1');
          }
      
      if(empty($result)){
            return $result[0][0];
      }else { 
             $url = $this->draWeek($table, $start, $finish, "week-" . $flag, $group);
        
              if($flag == 1) {
                    $array = array(
                        'first_week' =>  $url
                     );
              } else {
                    $array = array(
                      'second_week' =>  $url
                   );
              }
            $query_builder->update("user", $array, "user_id = " .$id);
             return $url;
      }
    }
}
?>