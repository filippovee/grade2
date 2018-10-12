<?php
    require_once 'Send.php';
    //Разрешаем кросс доменные запросы
    header('Access-Control-Allow-Origin: *');

    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

    header('Access-Control-Allow-Credentials: true');

    header('Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Accept, X-PINGOTHER, Content-Type');

    //получаем данные пост запроса
    $postdata=$_POST;
    $id=$postdata['id'];
    $sys = $postdata['sys'];
    $res='';


    //узнаём количество выбранных сделок
    foreach ($id as $key=>$value){
    $id_select[]=$value;
    }

    //если выбранны сделки и есть системная информация собираем массив
    if (isset($id) && isset($sys)) {
    $user = $sys['subdomain'];
    //заготовка на наш файл
    $csv = "{$user}.csv";

    //собираем ссылку для запроса к API
    for ($i = 0; $i < count($id_select); $i++) {
        $res .= "id%5B%5D=" . $id_select[$i] . "&";
    }
    $res = substr($res, 0, -1);
    $path_lead = 'leads?' . $res;
    //отправялем запрос к API, получаем список сделок
    $send = new Send($path_lead);
    $leads = $send->getlist_acc($path_lead);
    $leads = $leads['_embedded']['items'];

    //оставляем массив с интересующей нас информацией о сделках
    foreach ($leads as $key => $value) {
    //имя
        $leads_arr[$key]['name'] = $value['name'];
     //дата
        $leads_arr[$key]['created_at'] = date("d, m, Y", $value['created_at']);
     //id ктонатктов
        $leads_arr[$key]['contacts_name'] = $value['contacts']['id'];
     //id компаний
        $leads_arr[$key]['company_name'] = $value['company']['id'];
     //кастомные поля
        $leads_arr[$key]['custom_fields'] = $value['custom_fields'];
     //теги
        $leads_arr[$key]['tags'] = $value['tags'];

        //убираем лишние элементы
        foreach ($leads_arr[$key]['tags'] as $key_t => $value_t) {
            $leads_arr[$key]['tags'][$key_t] = $value_t['name'];
        }

    }
    //вытаскиваем заголовки
    $titles = array_keys($leads_arr[$key]);
 //    var_dump($titles);


    //Получаем названия компаний
    foreach ($leads_arr as $key => $value) {
        $comp_id[$key] = $value['company_name'];
    }
    $result = '';
    foreach ($comp_id as $key => $value) {
        $result .= "id[{$key}]=" . $value . "&";
    }

    $res = substr($result, 0, -1);
    $path_company = 'companies?' . $res;

    $company = $send->getlist_acc($path_company);
    $company = $company['_embedded']['items'];
    foreach ($company as $key => $value) {
        $leads_arr[$key]['company_name'] = $value['name'];
    }


    //Получаем названия котактов
    foreach ($leads_arr as $key => $value) {
        $cont_id[$key] = $value['contacts_name'];
    }
    // var_dump($cont_id);
    $result1 = '';
    foreach ($cont_id as $ids) {
        //собираем ссылку для запроса к API
        foreach ($ids as $i => $id) {
            $result1 .= "id%5B%5D=" . $id . "&";
        }
        //  var_dump($res1);
    }
    $res1 = substr($result1, 0, -1);
    $path_cont = 'contacts?' . $res1;
    //var_dump($path_cont);
    $cont = $send->getlist_acc($path_cont);
    $cont = $cont['_embedded']['items'];
    ///var_dump($cont);
    foreach ($cont as $key => $value) {
        $leads_arr[$key]['contacts_name'] = $value['name'];
    }

    //Получаем названия кастомных полей
    foreach ($leads_arr as $key => $value) {
        foreach ($leads_arr[$key]['custom_fields'] as $field => $info) {
            $leads_arr[$key]['custom_fields'][$field] = $info['name'];
            $custom_t[$field] = $info['name'] . " id=" . $info['id'];
            foreach ($info['values'] as $key_f => $value_f) {
                $leads_arr[$key][$info['name'] . " id=" . $info['id']][$key_f] = $value_f['value'];
                //если тип кастомного поля Юр. лицо, оно передаётся в виде массива
                if (is_array($leads_arr[$key][$info['name'] . " id=" . $info['id']][$key_f])) {
                    foreach ($leads_arr[$key][$info['name'] . " id=" . $info['id']][$key_f] as $k => $v) {
                        array_splice($leads_arr[$key][$info['name'] . " id=" . $info['id']], 0, -6);
                        $leads_arr[$key][$info['name'] . " id=" . $info['id']][$k] = "{$k}: {$v}";
                    }

                }
            }
            //имплодим, убираем лишние подмассивы
            //      var_dump($leads_arr[$key][$info['name']." id=".$info['id']]);
            $leads_arr[$key][$info['name'] . " id=" . $info['id']] = implode("\n", $leads_arr[$key][$info['name'] . " id=" . $info['id']]);
            //  var_dump($leads_arr[$key][$info['name']." id=".$info['id']]);
        }

    }
    // ещё убираем лишние массивы
    foreach ($leads_arr as $key => $value) {
        $leads_arr[$key]['custom_fields'] = implode(", ", $value['custom_fields']);
        $leads_arr[$key]['tags'] = implode(", ", $value['tags']);
    }



   //собираем массив с заголовками
      //  var_dump($custom_t);
    $titles = array_merge($titles, $custom_t);
    $titles = array_flip($titles);

    foreach ($titles as $key => $value) {
        $titles[$key] = "";
    }
    //Создаём CSV файл
        //Открываем файл только для записи; помещает указатель в начало файла и обрезает файл до нулевой длины. Если файл не существует - пробует его создать.
    $open = fopen($csv, 'w');
        // решаем проблему кодировки русских букв
        fputs($open, chr(0xEF) . chr(0xBB) . chr(0xBF));
        //создаём CSV. указываем файл открытый fopen, массив строк, разделитель
    fputcsv($open, array_keys($titles), ";");

   //    var_dump($open);
    $count = count($leads_arr);
    for ($i=0; $i < $count; $i++) {
        //сливаем массив с заголовками и массив с информацией
        $lead = array_shift($leads_arr);
        $row = array_merge($titles, $lead);
        fputcsv($open, $row, ";");
    }
 //   var_dump($lead);
     //   var_dump($open);
    fclose($open);
    echo $csv;


    } else {
    echo "error";
    }
