<?php

class fa {
    /**
     * Database handler
     *
     * @var resource
     */

    const GR1 = 600;
    const GR2 = 999;
    const GR_UDF = 1000;
    const IMVIEW = 10000;

    // время блокировки в секундах
    const BLOCKEDTIME = 300;
    // время запросов в секундах
    const TIMEPERREQUESTS = 60;


    private $dbh;

    public function __construct(){

        $host = '172.22.251.2'; // основной сервер
        //$host = '172.22.251.3'; // тестовый
        $s = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = $host)(PORT = 1521))
        (CONNECT_DATA =
          (SERVER = DEDICATED)
          (SID = test11)
        )
        )";
        $this->dbh = oci_pconnect('a', 'b', $s, 'AL32UTF8');

        if (!$this->dbh) {
            $e = oci_error();
            // smtp_mail('teplyakov.m@forum-auto.ru', 'fa. __construct', "<br>������ ���� ������ ORACLE. " . $e['message']);
        } else {
            // smtp_mail('teplyakov.m@forum-auto.ru', 'fa. __construct', "<br>ORACLE connected!");
            $stid = oci_parse($this->dbh, "ALTER SESSION SET NLS_DATE_FORMAT = 'dd.mm.yyyy hh24:mi:ss'");
            oci_execute($stid);
        }
    }



    /**
     *
     * @param $jrlid
     * @param $tovnum
     * @return array
     */
    public function getTov($jrlid, $tovnum, $cross1=0, $login, $password){

       
        

        if(!empty($login) && !empty($password)){

            $resAuth = $this->checkAuth($jrlid, $login, $password);

            //return $resAuth;

            if($resAuth['errorCode'] != 102 && $resAuth['errorCode'] != 103){

                if($cross1==1){
                    $partSQL = $this->partSQL();
                }

                // вызов запроса
                $part = "t.nru=:nru";
                $sql = $this->SQL($partSQL, $part);

                $stid = oci_parse($this->dbh, $sql);

                $gr1 = self::GR1;
                $gr2 = self::GR2;
                $gr_udf = self::GR_UDF;
                $imview = self::IMVIEW;
                $jrlid = $resAuth[2];
                $sa = $resAuth[3]; // включен са = 1, иначе 0
                $PakSk = [];

                oci_bind_by_name($stid, 'nru', $tovnum);
                oci_bind_by_name($stid, 'gr_udf', $gr_udf);
                oci_bind_by_name($stid, 'imview', $imview);
                oci_bind_by_name($stid, 'jrlid', $jrlid);
                oci_bind_by_name($stid, 'gr1', $gr1);
                oci_bind_by_name($stid, 'gr2', $gr2);

                oci_execute($stid);

                //   $res[0][] = $resAuth[0]; // записываем 1 строку из массива $resAuth

                $main = [];

                while ($row = oci_fetch_array($stid, OCI_BOTH + OCI_RETURN_NULLS)) {

                    if($row['NUM'] < 0) { $row['NUM'] = 0; }

                    // подключение функций сторонних ассортиментов
                    if($sa > 0 && $row['NUM'] == 0 && ($row['FIRST'] == 0 || $row['BR_SA'] > 0) )
                    {

                        $sas = $this->sa($row['TOVID'],  $jrlid);
                        
//                        return $sas;
                        
                        if(count($sas) > 0) {
//                          $s = 0;
                            foreach ($sas AS $sa){
//                                
//                          $s++;
//                          $item_exp = $row;
                                $item_exp['TOVNUM'] = $sa['TID'];
                                $item_exp['BRAND'] = $row['BRAND'];
                                $item_exp['NR'] = $row['NR'];
                                $item_exp['NAME'] = $row['NAME'];
                                $item_exp['D_DELIV']  = ($sa['DAYSGETALL'] + $this->get_ddeliv($sa['SKIDPACK'], $jrlid));
                                $item_exp['KR'] = $sa['KR'];
                                $item_exp['PRICE'] = $sa['CENACF'];
                                $item_exp['NUM'] = $sa['K'];

                                //  $item_exp['sa_price'] = $sa['CENACF'];
                                //  $item_exp['d_deliv'] = $sa['d_deliv'];
                                //  $item_exp['sid_name'] = 'Ст.ассорт., прайс:'.$sa['idprice'];
                                //  $item_exp['inbox'] = $sa['kr'];
                                //  $item_exp['paklid'] = $s;
                                //  $item_exp['ucen'] = 0;
                                
 

                                
//                                if ($item_exp['NUM'] > 0  ) {
                                    
//                                    $PakSk_row = array(
//                                        'paklid' => $item_exp['paklid'],
//                                        'num' => $item_exp['NUM'],
//                                        'inbox' => $item_exp['inbox']
//                                    );
                                    
                                    //$ukey = $item_exp['good_id'] . '_' . $item_exp['sid'] . '_0' . '_' . $item_exp['sa_price'];
                                    
                                    // Если ст.асс. вернул второй одинаковый элемент, не добавлять его в вывод
//                                    if (count($PakSk[$ukey]) > 0 ) {
//                                        continue;
//                                    }
//                                    
//                                    $PakSk[$ukey][] = $PakSk_row ;


//                                    if (count($PakSk[$ukey]) > 1 ) { continue; }
//
//                                }
                                //     Массивы для сортировки
                                //  $grs[] = -10; // первая группа
                                //  $cat[] = 0; // на втором месте, после нашего товара. было 2 1.08.2016
                                //  $nums[] = 1;
                                //  $prices[] = $item_exp['price'];
                                //  $item_exp['return'] = $returnNo;

                                //array_push($res, $item_exp);
                                //$res[1][] = $sas;
                                $res[1][] = $item_exp;
                                //  $res[0][] = $sa;
                            }
                        }

                    }
                        
                 //   return $sas;
                 
                    
                    

                    // не выводить c ценой<0! именно $row['PRICE']
                    // не выводить товар, у которого нет ни одной упаковки А.Б.
                    // не выводить нулевые. Ожидаемый товар дальше по циклу не пойдет, он уже в $res_arr.
                    if (!($row['PRICE'] > 0) || !($row['PAKLID'] > 0) || !($row['NUM'] > 0) )
                    {
                        continue;
                    }
                    else{

                        $main['TOVNUM'] = $row['TOVID'];
                        $main['BRAND'] = $row['BRAND'];
                        $main['NR'] = $row['NR'];
                        $main['NAME'] = $row['NAME'];
                        $main['D_DELIV'] = $row['D_DELIV'];
                        $main['KR'] = $row['KR'];
                        $main['PRICE'] = $row['PRICE'];
                        
//                        $main['PAKLID'] = $row['PAKLID'];
//                        $main['UCEN'] = $row['UCEN'];
                        
                        if($row['NUM'] > 30 && $row['KR'] * 30 <= $row['NUM']){
                            $row['NUM'] = $row['KR'] * 30;
                        }
                        $main['NUM'] = $row['NUM'];

                        $res[1][] = $main;
                    }

                }

                
            //    return $ddd;
                
                
                // проверка
                $rescheck = (!empty($res)) ? true : false;

                $resultcheck = $this->checkRight($rescheck);

                $this->saveLog($jrlid, $tovnum, $brand=null, $resultcheck);

                //$res= $log;

                return $res;
            }
            else {

                if($resAuth['errorCode'] == 102){
                    $resAuth = $this->errorAuth();
                }
                if($resAuth['errorCode'] == 103){
                    $resAuth = $this->accessDenied();
                }


                return $resAuth;
            }
        }

    }

    /**
     *
     * @param $jrlid
     * @param $tovnum
     * @param $brand
     * @return array
     */
    public function getTov1($jrlid, $tovnum, $brand, $cross2=0, $login, $password){


        if(!empty($login) && !empty($password)){

            $resAuth = $this->checkAuth($jrlid, $login, $password);

            if($resAuth['errorCode'] != 102 && $resAuth['errorCode'] != 103){

                if($cross2==1){
                    $partSQL = $this->partSQL($partSQL);
                }

                $part = "t.nru=:nru";
                $brandSQL = "AND br.name=:brand";
                $sql = $this->SQL($partSQL, $part, $brandSQL);

                $stid = oci_parse($this->dbh, $sql);

                $gr1 = self::GR1;
                $gr2 = self::GR2;
                $gr_udf = self::GR_UDF;
                $imview = self::IMVIEW;
                $jrlid = $resAuth[2];
                $sa = $resAuth[3]; // включен са = 1, иначе 0
                $PakSk =[];

                oci_bind_by_name($stid, 'nru', $tovnum);
                oci_bind_by_name($stid, 'gr_udf', $gr_udf);
                oci_bind_by_name($stid, 'imview', $imview);
                oci_bind_by_name($stid, 'jrlid', $jrlid);
                oci_bind_by_name($stid, 'gr1', $gr1);
                oci_bind_by_name($stid, 'gr2', $gr2);
                oci_bind_by_name($stid, 'brand', $brand);

                oci_execute($stid);

                //  $res[0][] = $resAuth[0];
                $main = [];

                while ($row = oci_fetch_array($stid, OCI_BOTH + OCI_RETURN_NULLS)) {

                    if($row['NUM'] < 0) { $row['NUM'] = 0; }

                    // подключение функций сторонних ассортиментов
                    if($sa>0 && $row['NUM'] == 0 && ($row['FIRST'] == 0 || $row['BR_SA'] > 0))
                    {

                        $sas = $this->sa($row['TOVID'], $jrlid);

                        if(count($sas) > 0) {
                            // $s = 0;
                            foreach ($sas AS $sa){
                                //     $s++;
                                //     $item_exp = $row;
                                $item_exp['TOVNUM'] = $sa['TID'];
                                $item_exp['BRAND'] = $row['BRAND'];
                                $item_exp['NR'] = $row['NR'];
                                $item_exp['NAME'] = $row['NAME'];
                                $item_exp['D_DELIV']  = ($sa['DAYSGETALL'] + $this->get_ddeliv($sa['SKIDPACK'], $jrlid));
                                $item_exp['KR'] = $sa['KR'];
                                $item_exp['PRICE'] = $sa['CENACF'];
                                $item_exp['NUM'] = $sa['K'];
                                //  $item_exp['sid'] = $sa['TID'];
                                //  $item_exp['num'] = $sa['K'];
                                //  $item_exp['price'] = $sa['price'];
                                //  $item_exp['sa_price'] = $sa['CENACF'];
                                //  $item_exp['d_deliv'] = $sa['d_deliv'];
                                //  $item_exp['sid_name'] = 'Ст.ассорт., прайс:'.$sa['idprice'];
                                //  $item_exp['inbox'] = $sa['kr'];
                                //  $item_exp['paklid'] = $s;
                                //  $item_exp['ucen'] = 0;
                                //  $item_exp = $row;
                                //  $item_exp['sid'] = $sa['sid'];
                                //  $item_exp['num'] = $sa['num'];
                                //  $item_exp['price'] = $sa['price'];
                                //  $item_exp['sa_price'] = $sa['sa_price'];
                                //  $item_exp['d_deliv'] = $sa['d_deliv'];
                                //  $item_exp['sid_name'] = 'Ст.ассорт., прайс:'.$sa['idprice'];
                                //  $item_exp['inbox'] = $sa['kr'];
                                //  $item_exp['paklid'] = $s;
                                //  $item_exp['ucen'] = 0;
//                                if ($item_exp['NUM'] > 0  ) {
//                                    $PakSk_row = array(
//                                        'paklid' => $item_exp['paklid'],
//                                        'num' => $item_exp['NUM'],
//                                        'inbox' => $item_exp['inbox']);
//                                    $ukey = $item_exp['good_id'] . '_' . $item_exp['sid'] . '_0' . '_' . $item_exp['sa_price'];
//                                    // Если ст.асс. вернул второй одинаковый элемент, не добавлять его в вывод
//                                    if (count($PakSk[$ukey]) > 0 ) {
//                                        continue;
//                                    }
//                                    $PakSk[$ukey][] = $PakSk_row ;
//
//                                }
                                //     Массивы для сортировки
                                //  $grs[] = -10; // первая группа
                                //  $cat[] = 0; // на втором месте, после нашего товара. было 2 1.08.2016
                                //  $nums[] = 1;
                                //  $prices[] = $item_exp['price'];
                                //  $item_exp['return'] = $returnNo;

                                //array_push($res, $item_exp);

                                $res[1][] = $item_exp;
                            }
                        }

                    }

                    // не выводить c ценой<0! именно $row['PRICE']
                    // не выводить товар, у которого нет ни одной упаковки А.Б.
                    // не выводить нулевые. Ожидаемый товар дальше по циклу не пойдет, он уже в $res_arr.
                    if (!($row['PRICE'] > 0) || !($row['PAKLID'] > 0) || !($row['NUM'] > 0 ))
                    {
                        continue;
                    }
                    else{

                        $main['TOVNUM'] = $row['TOVID'];
                        $main['BRAND'] = $row['BRAND'];
                        $main['NR'] = $row['NR'];
                        $main['NAME'] = $row['NAME'];
                        $main['D_DELIV'] = $row['D_DELIV'];
                        $main['KR'] = $row['KR'];
                        $main['PRICE'] = $row['PRICE'];
                        if($row['NUM'] > 30 && $row['KR'] * 30 <= $row['NUM']){
                            $row['NUM'] = $row['KR'] * 30;
                        }
                        $main['NUM'] = $row['NUM'];

                        $res[1][] = $main;
                    }

                }


                

                // проверка
                (!empty($res))? $rescheck = true : $rescheck = false;

                $resultcheck = $this->checkRight($rescheck);

                $this->saveLog($jrlid, $tovnum, $brand, $resultcheck);

                return $res;
            }
            else {
                if($resAuth['errorCode'] == 102){
                    $resAuth = $this->errorAuth();
                }
                if($resAuth['errorCode'] == 103){
                    $resAuth = $this->accessDenied();
                }

                return $resAuth;
            }
        }
    }

    /**
     *
     * @param $keytov
     * @return array
     */
    public function getTov2($jrlid, $keytov, $cross3=0, $login, $password){

        if(!empty($login) && !empty($password)){

            $resAuth = $this->checkAuth($jrlid, $login, $password);

            if($resAuth['errorCode'] != 102 && $resAuth['errorCode'] != 103){

                if($cross3==1){
                    $partSQL = $this->partSQL();
                }

                // вызов запроса
                $part = "t.id=:id";
                $sql = $this->SQL($partSQL, $part);

                $stid = oci_parse($this->dbh, $sql);

                $gr1 = self::GR1;
                $gr2 = self::GR2;
                $gr_udf = self::GR_UDF;
                $imview = self::IMVIEW;
                $jrlid = $resAuth[2];
                $sa = $resAuth[3]; // включен са = 1, иначе 0

                oci_bind_by_name($stid, 'id', $keytov); // по ключу
                oci_bind_by_name($stid, 'gr_udf', $gr_udf);
                oci_bind_by_name($stid, 'imview', $imview);
                oci_bind_by_name($stid, 'jrlid', $jrlid);
                oci_bind_by_name($stid, 'gr1', $gr1);
                oci_bind_by_name($stid, 'gr2', $gr2);

                oci_execute($stid);

                //  $res[0][] = $resAuth[0];
                $main = [];

                while ($row = oci_fetch_array($stid, OCI_BOTH + OCI_RETURN_NULLS)) {

                    if($row['NUM'] < 0) { $row['NUM'] = 0; }

                    // подключение функций сторонних ассортиментов
                    if($sa > 0 && $row['NUM'] == 0 && ($row['FIRST'] == 0 || $row['BR_SA'] > 0))
                    {

                        $sas = $this->sa($row['TOVID'], $jrlid);

                        if(count($sas) > 0) {
                            //$s = 0;
                            foreach ($sas AS $sa){
                                //   $s++;
                                //   $item_exp = $row;
                                $item_exp['TOVNUM'] = $sa['TID'];
                                $item_exp['BRAND'] = $row['BRAND'];
                                $item_exp['NR'] = $row['NR'];
                                $item_exp['NAME'] = $row['NAME'];
                                $item_exp['D_DELIV']  = ($sa['DAYSGETALL'] + $this->get_ddeliv($sa['SKIDPACK'], $jrlid));
                                $item_exp['KR'] = $sa['KR'];
                                $item_exp['PRICE'] = $sa['CENACF'];
                                $item_exp['NUM'] = $sa['K'];
                                //  $item_exp['sid'] = $sa['TID'];
                                //  $item_exp['num'] = $sa['K'];
                                //  $item_exp['price'] = $sa['price'];
                                //  $item_exp['sa_price'] = $sa['CENACF'];
                                //  $item_exp['d_deliv'] = $sa['d_deliv'];
                                //  $item_exp['sid_name'] = 'Ст.ассорт., прайс:'.$sa['idprice'];
                                //  $item_exp['inbox'] = $sa['kr'];
                                //  $item_exp['paklid'] = $s;
                                //  $item_exp['ucen'] = 0;
                                //  $item_exp = $row;
                                //  $item_exp['sid'] = $sa['sid'];
                                //  $item_exp['num'] = $sa['num'];
                                //  $item_exp['price'] = $sa['price'];
                                //  $item_exp['sa_price'] = $sa['sa_price'];
                                //  $item_exp['d_deliv'] = $sa['d_deliv'];
                                //  $item_exp['sid_name'] = 'Ст.ассорт., прайс:'.$sa['idprice'];
                                //  $item_exp['inbox'] = $sa['kr'];
                                //  $item_exp['paklid'] = $s;
                                //  $item_exp['ucen'] = 0;
//                                if ($item_exp['NUM'] > 0  ) {
//                                    $PakSk_row = array(
//                                        'paklid' => $item_exp['paklid'],
//                                        'num' => $item_exp['NUM'],
//                                        'inbox' => $item_exp['inbox']);
//                                    $ukey = $item_exp['good_id'] . '_' . $item_exp['sid'] . '_0' . '_' . $item_exp['sa_price'];
//                                    // Если ст.асс. вернул второй одинаковый элемент, не добавлять его в вывод
//                                    if (count($PakSk[$ukey]) > 0 ) {
//                                        continue;
//                                    }
//                                    $PakSk[$ukey][] = $PakSk_row ;
//
//                                }
                                //     Массивы для сортировки
                                //  $grs[] = -10; // первая группа
                                //  $cat[] = 0; // на втором месте, после нашего товара. было 2 1.08.2016
                                //  $nums[] = 1;
                                //  $prices[] = $item_exp['price'];
                                //  $item_exp['return'] = $returnNo;

                                //array_push($res, $item_exp);

                                $res[1][] = $item_exp;
                            }
                        }

                    }

                    // не выводить c ценой<0! именно $row['PRICE']
                    // не выводить товар, у которого нет ни одной упаковки А.Б.
                    // не выводить нулевые. Ожидаемый товар дальше по циклу не пойдет, он уже в $res_arr.
                    if (!($row['PRICE'] > 0) || !($row['PAKLID'] > 0) || !($row['NUM'] > 0 ))
                    {
                        continue;
                    }
                    else{

                        $main['TOVNUM'] = $row['TOVID'];
                        $main['BRAND'] = $row['BRAND'];
                        $main['NR'] = $row['NR'];
                        $main['NAME'] = $row['NAME'];
                        $main['D_DELIV'] = $row['D_DELIV'];
                        $main['KR'] = $row['KR'];
                        $main['PRICE'] = $row['PRICE'];
                        if($row['NUM'] > 30 && $row['KR'] * 30 <= $row['NUM']){
                            $row['NUM'] = $row['KR'] * 30;
                        }
                        $main['NUM'] = $row['NUM'];

                        $res[1][] = $main;

                    }

                }




                // проверка если запрос что-то нашел
                (!empty($res))? $rescheck = true : $rescheck = false;
                //вызов метода
                $resultcheck = $this->checkRight($rescheck);

                //вызов метода сохранения в журнал
                $this->saveLog($jrlid, $tovnum, $brand, $resultcheck);


                return $res;

            }
            else {
                if($resAuth['errorCode'] == 102){
                    $resAuth = $this->errorAuth();
                }
                if($resAuth['errorCode'] == 103){
                    $resAuth = $this->accessDenied();
                }

                return $resAuth;
            }
        }
    }

    /**
     *
     * @param $jrlid
     * @return bool
     */
    public function  checkRight($rescheck){

        ($rescheck)? $resultcheck = 20 : $resultcheck = 10;

        return $resultcheck;
    }

    public function checkAuth($jrlid, $login, $password){

        $jrlid = strip_tags(trim($jrlid));
        $login = strip_tags(trim($login));
        $password = strip_tags(trim($password));

        $sql ="SELECT * FROM WEBS WHERE JRLID=:jrlid AND LOGIN = :login AND PASS = :password";

        $stid = oci_parse($this->dbh, $sql);

        oci_bind_by_name($stid, 'jrlid', $jrlid);
        oci_bind_by_name($stid, 'login', $login);
        oci_bind_by_name($stid, 'password', $password);

        oci_execute($stid);

        $res1=[];
        while (($row = oci_fetch_array($stid, OCI_ASSOC)) != false) {
            $res1 = $row;
        }

        //если пароль и логин прошел
        if (!empty($res1)) {

            //текущее время
            $currentTime = time();

            //время последнего запроса
            $dateTIMEQUERY = strtotime($res1['TIMEQUERY']);

            //разница времени между текущим временем и временем последнего запроса
            $diff = $currentTime - $dateTIMEQUERY;

            //счетчик
            $counter = $res1['CNTQUERIES'];

            //время блокировки
            $timeBlock = strtotime($res1['TIMEBLOCK']);

            //если превысил количество запросов
            if($counter >= $res1['QTQUERY'] ){

                //проверка по количеству запросов по времени
                if($diff > self::TIMEPERREQUESTS && $timeBlock == null ){

                    //обнуляем счетчик записываем дату обнуления
                    $date = date('d.m.Y H:i:s');

                    $sql = "UPDATE WEBS SET CNTQUERIES = 0 , TIMEQUERY =:DATEQ , TIMEBLOCK = null WHERE id =:ID";
                    $stid = oci_parse($this->dbh, $sql);
                    oci_bind_by_name($stid, 'ID', $res1['ID']);
                    oci_bind_by_name($stid, 'DATEQ', $date);

                    oci_execute($stid);

                }
                else{

                    //время блокировки
                    //  $dateBlocked = strtotime($res1['TIMEBLOCK']);

                    //разница времени между текущим временем и временем последнего запроса
                    //  $diff = $currentTime - $dateBlocked;


                    //снимаем блокировку
                    if($diff > self::BLOCKEDTIME){

                        //обнуляем счетчик записываем дату обнуления
                        $date = date('d.m.Y H:i:s');

                        $sql = "UPDATE WEBS SET CNTQUERIES = 0 , TIMEQUERY =:DATEQ , TIMEBLOCK = null WHERE id =:ID";
                        $stid = oci_parse($this->dbh, $sql);
                        oci_bind_by_name($stid, 'ID', $res1['ID']);
                        oci_bind_by_name($stid, 'DATEQ', $date);

                        oci_execute($stid);

                        $res[] = 'Вы разблокированы!';

                    }
                    // устанавливаем дату блокировки
                    else{
                        $date = date('d.m.Y H:i:s');
                        $sql = "UPDATE WEBS SET TIMEBLOCK = :DATEQ WHERE id =:ID";
                        $stid = oci_parse($this->dbh, $sql);
                        oci_bind_by_name($stid, 'ID', $res1['ID']);
                        oci_bind_by_name($stid, 'DATEQ', $date);

                        oci_execute($stid);

                        $resAuth = $this->accessDenied();

                        return $resAuth;
                        //$res[] = 'Вы превысили количество запросов, повторите снова через '.self::BLOCKEDTIME.' сек.';
                        //  exit;
                    }

                }

            }
            else
            {

                $counter++;

                $rest = $res1['QTQUERY'] - $counter;

                $date = date('d.m.Y H:i:s');

                $sql = "UPDATE WEBS SET CNTQUERIES =:COUNTER , TIMEQUERY =:DATEQ  WHERE id =:ID";

                $stid = oci_parse($this->dbh, $sql);

                oci_bind_by_name($stid, 'ID', $res1['ID']);
                oci_bind_by_name($stid, 'COUNTER', $counter);
                oci_bind_by_name($stid, 'DATEQ', $date);

                oci_execute($stid);

//$resAuth ="asdasd";
                //$res[] = $counter;
                $res[] = "Количество запросов за ".self::TIMEPERREQUESTS." сек. не больше ". $res1['QTQUERY'].", осталось ". $rest;
                $res[] = $res1['QTQUERY'];
                $res[] = $res1['JRLID'];
                $res[] = $res1['SA'];

                return $res;

            }


        }
        else{

            $res =  $this->errorAuth();
            return $res;
        }


    }

    /**
     *
     * @param $jrlid
     * @param $tovnum
     * @return bool
     */
    public function saveLog($jrlid, $tovnum, $brand=null, $resultcheck){

        //  return $brand;

        $gr1 = self::GR1;
        $gr2 = self::GR2;

        $ip = $_SERVER['REMOTE_ADDR'];
        $date = date("d.m.y H:i:s");

        $sql = "INSERT INTO IM_LOGQ (WID, IP, DT, BR, NR, RES, GR1, GR2, TPID) VALUES (:WID,:IP,:DT,:BR,:NR,:RES,:GR1,:GR2, 2)";

        // $sql = "INSERT INTO IM_LOGQ (WID, IP) VALUES ('555',:IP)";

        $stid = oci_parse($this->dbh, $sql);

        oci_bind_by_name($stid, 'WID', $jrlid);
        oci_bind_by_name($stid, 'IP', $ip);
        oci_bind_by_name($stid, 'DT', $date);
        oci_bind_by_name($stid, 'BR', $brand);
        oci_bind_by_name($stid, 'NR', $tovnum);
        oci_bind_by_name($stid, 'RES', $resultcheck);
        oci_bind_by_name($stid, 'GR1', $gr1);
        oci_bind_by_name($stid, 'GR2', $gr2);

        oci_execute($stid);

        return  true;
    }

    public function partSQL(){

        $partSQL = "SELECT c.cid
      FROM tcr c
      WHERE c.tid IN (SELECT id FROM tids)
      UNION ALL
      SELECT c.tid
      FROM tcr c
      WHERE c.cid IN (SELECT id FROM tids)
      UNION ALL";

        return $partSQL;

    }

    public function SQL($partSQL, $part, $brandSQL=null){

        $sql = "WITH
                tids as (SELECT t.id  id FROM tov t WHERE ".$part." AND t.grid NOT IN (SELECT id FROM tovgr WHERE imnotshow=1)),
                tids1 as (
                  ".$partSQL."
                  SELECT id FROM tids),
                dr as (
                  SELECT d.adrid, d.defsid
                  FROM klj k
                  JOIN depadr d ON d.depid=k.depid
                  WHERE k.id=:jrlid),
                uct as(
                  SELECT ut.ucen_casual,ut.tid, NVL(ut.tovpakl,0) as ucenpakl
                  FROM ucen_tov ut
                  WHERE ut.tid IN (SELECT * FROM tids1)),
                pakl_sk AS (
                  SELECT  MIN(l.id) AS id,
                            l.tid, l.kr AS kr, rs.adrid AS sid,
                            NVL(ut.ucenpakl,0) AS ucen,
                            (SELECT name FROM ucen_casual uc where id=ut.ucen_casual) AS ucen_casual,
                            skn.kb AS num

                    FROM tovpakl l
                    LEFT JOIN uct ut on l.id = ut.ucenpakl
                    JOIN dr rs on rs.adrid>0

                    JOIN (
                      SELECT s1.adrid,s1.tid,sum(s1.kb) AS kb, s1.kr, s1.ucenpakl
                        FROM (
                          SELECT l1.sid adrid, -coalesce((sum(l1.k2)-sum(l1.k3)),0) AS kb, l1.tid AS tid, l1.kr, coalesce(u1.ucenpakl,0) as ucenpakl
                            FROM doc d1
                            JOIN docl l1 ON l1.did=d1.id
                            LEFT JOIN uct u1 on u1.ucenpakl=l1.paklid
                            WHERE d1.tpk IN ('N','K') AND d1.stk IN ('B','V','C')
                                  AND l1.tid IN (SELECT * FROM tids1)
                            GROUP BY l1.sid,l1.tid,l1.kr, coalesce(u1.ucenpakl,0)
                          UNION ALL
                          SELECT a2.sid adrid, sum(s2.k3) AS kb,  s2.tovid AS tid, p2.kr, coalesce(u2.ucenpakl,0) as ucenpakl
                            FROM sk s2
                            JOIN tovpakl p2 on p2.id=s2.tovpaklid
                            JOIN adr a2 on s2.adrid=a2.id
                            LEFT JOIN uct u2 on u2.ucenpakl=s2.tovpaklid
                            WHERE  s2.tovid IN (SELECT * FROM tids1)
                                   AND s2.stid=1
                            GROUP BY a2.sid,s2.tovid, p2.kr, coalesce(u2.ucenpakl,0)
                            HAVING sum(s2.k3)>0
                      )s1
                      GROUP BY s1.adrid,s1.tid, s1.kr, s1.ucenpakl

                    ) skn ON skn.adrid=rs.adrid AND skn.tid=l.tid AND skn.ucenpakl=NVL(ut.ucenpakl,0) and skn.kr=l.kr

                    WHERE l.tid IN (SELECT * FROM tids1)
                        AND l.kratp=1

                    GROUP BY l.kr, l.tid, ut.ucenpakl, rs.adrid, ucen_casual, skn.kb

                    ORDER BY l.kr
                )


              SELECT

                CASE WHEN ".$part." THEN 0 ELSE 1 END AS first,

                t.id AS tovid,
                t.nr AS nr,
                o.name || NVL(l.ucen_casual,'') AS name,
                o.prim AS models,
                coalesce(br.prname, br.name)  AS brand,

                CASE WHEN t.STID =1 THEN 1 ELSE 0 END AS analog,

                (SELECT MAX(d.srdost) FROM kljrl k JOIN depadr d ON k.depid = d.depid  WHERE k.id=:jrlid AND d.adrid=l.sid) AS d_deliv,

                l.id AS paklid,
                l.ucen AS ucen,


                CASE
                  WHEN l.ucen > 0
                    THEN nvl(getcendt(t.id, :jrlid, SYSDATE, 0, l.id),0)
                    ELSE nvl(getcendt(t.id, :jrlid, SYSDATE, 0),0)
                END AS price,
                l.num AS num,
                l.kr  AS kr

              FROM tov t
              LEFT JOIN pakl_sk l ON l.tid=t.id

              JOIN (
                select o.tid tid,o.name name, o.prim  prim
                  from tovop o where o.tid in (SELECT * FROM tids1))o ON o.tid=t.id

              JOIN (select id, name, prname from br
                      where not exists(select 1 from brx b where b.brid=br.id and b.depid IN (SELECT MAX(depid) FROM kljrl WHERE id=:jrlid))) br on br.id=t.brid

              JOIN (
                SELECT tg.id
                  FROM tovgr tg
                  START WITH tg.id in (SELECT id FROM tovgr WHERE imview=:imview)
                  CONNECT BY PRIOR tg.id=tg.parentid
                UNION
                SELECT tg.id
                  FROM tovgr tg
                  START WITH tg.id in(:gr1,:gr2, :gr_udf)
                  CONNECT BY PRIOR tg.id=tg.parentid
                ) g ON g.id=t.grid

                WHERE t.id IN (select * from tids1) ".$brandSQL;


        return $sql;
    }

// функция поиска по стороннему ассортименту
    function sa($good_id, $wid) {

        $res = [];

//        if($sa_price_id > 0){            
//
//            $sql = "
//                  SELECT * FROM (
//                      SELECT *
//                        FROM TABLE(sa_from_web('$good_id',sysdate,'$wid'))
//                      UNION ALL
//                      SELECT *
//                        FROM TABLE(sa_from_web_speed('$good_id',sysdate,'$wid'))
//                  ) t WHERE t.id='$sa_price_id'
//                    ";
//        } else {

            $sql = "
                  SELECT *
                    FROM TABLE(sa_from_web('$good_id',sysdate,'$wid'))
                   UNION
                  SELECT *
                    FROM TABLE(sa_from_web_speed('$good_id',sysdate,'$wid'))
                    ";
//        }

        //    $start_time1 = microtime(true);

        $stid = oci_parse($this->dbh, $sql);
        $e = oci_execute($stid);

        while (($row = oci_fetch_array($stid, OCI_ASSOC)) != false) {
            $res[] = $row;
        }

     
        

        //    $exec_time1 = microtime(true) - $start_time1;
        //devprint('Время поиска ст.асс и расчета дней доставки: ' . round($exec_time1, 4) . ' сек. $good_id='.$good_id);
//        if($sa_price_id > 0){
//            return $res[0];
//        } else {
            return $res;
//        }

    }

    /**
     * Дней доставки для склада
     * @param $sid
     * @return string
     */
    function get_ddeliv($sid, $jrlid) {

        $sql = "SELECT MAX(d.srdost) AS dd FROM kljrl k JOIN depadr d ON k.depid = d.depid  WHERE k.id=:WID AND d.adrid=:SID";

        $stid = oci_parse($this->dbh, $sql);

        oci_bind_by_name($stid, 'WID', $jrlid);
        oci_bind_by_name($stid, 'SID', $sid);

        $res = oci_execute($stid);

        // $res = [];
        //
        // while (($row = oci_fetch_array($stid, OCI_ASSOC)) != false) {
        //     $res[] = $row;
        // }


        // if (isset($row[0])) {
        //     $res = $row[0]['dd'];
        // } else {
        //     $res = '';
        // }

        // return $row;
        return $res;
    }

    //функции ошибок
    //ошибка аутентификации
    function errorAuth() {

        $res = [];
        $res['errorCode'] = 102;
        $res['errorMessage'] = 'User Authentication Error';

        return $res;
    }

    //ошибка доступ запрещен, превышен лимит запросов
    function accessDenied() {
        $res = [];
        $res['errorCode'] = 103;
        $res['errorMessage'] = 'Access denied, exceeded limit of requests';

        return $res;
    }





}