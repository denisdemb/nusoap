<?php
header("Content-type:text/html; Charset=Utf-8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Web-service проценки</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
</head>
<style>
  th {
      text-align: center;
  }
  .error{
    color:red;
    font-size: 16px;
    text-align: center;
  }
  #chk1, #chk2, #chk3 {
    vertical-align: sub;
  }
</style>
<body>
  <div class="col-md-offset-1 col-md-10 col-md-offset-1">
    <?php
        echo "<h1>Web-service проценки</h1><hr>";

        $tovnum1 = strip_tags(trim(strtoupper($_POST['tovnum1'])));
        $tovnum1 = preg_replace('%[^a-z\d]%i', '', $tovnum1);
        $jrlid1 = strip_tags(trim($_POST['jrlid1']));
        $jrlid1 = preg_replace('%[^0-9\d]%i', '', $jrlid1);
        $cross1 = ($_POST['cross1'])? 1:0;
        $login1 = strip_tags(trim($_POST['login1']));
        $password1 = strip_tags(trim($_POST['password1']));

        $tovnum2 = strip_tags(trim(strtoupper($_POST['tovnum2'])));
        $tovnum2 = preg_replace('%[^a-z\d]%i', '', $tovnum2);
        $jrlid2 = strip_tags(trim($_POST['jrlid2']));
        $jrlid2 = preg_replace('%[^0-9\d]%i', '', $jrlid2);
        $cross2 = ($_POST['cross2'])? 1:0;
        $brand = strip_tags(trim(strtoupper($_POST['brand'])));
        $brand = preg_replace('%[^a-z\d]%i', '', $brand);
        $login2 = strip_tags(trim($_POST['login2']));
        $password2 = strip_tags(trim($_POST['password2']));

        $tovnum3 = strip_tags(trim(strtoupper($_POST['tovnum3'])));
        $tovnum3 = preg_replace('%[^a-z\d]%i', '', $tovnum3);
        $jrlid3 = strip_tags(trim($_POST['jrlid3']));
        $jrlid3 = preg_replace('%[^0-9\d]%i', '', $jrlid3);
        $cross3 = ($_POST['cross3'])? 1:0;
        $keytov = strip_tags(trim(strtoupper($_POST['keytov'])));
        $keytov = preg_replace('%[^0-9\d]%i', '', $keytov);
        $login3 = strip_tags(trim($_POST['login3']));
        $password3 = strip_tags(trim($_POST['password3']));

    ?>
        <form action="client.php" method="post" >
          <div style='display:inline:block'>
            id юр.лица:
            <input type=text name='jrlid1' class="clear1" value="<?=$jrlid1;?>" required="required">&nbsp;&nbsp;
            код производителя:
            <input type=text name='tovnum1' class="clear1" value="<?=$tovnum1;?>" required="required">&nbsp;&nbsp;
            кросс:
            <input type="checkbox" name='cross1' id="chk1" value="1" <?php if($cross1==1) echo'checked'; ?>>&nbsp;&nbsp;
            логин:
            <input type=text name='login1' class="clear1" value="<?=$login1;?>" required="required">&nbsp;&nbsp;
            пароль:
            <input type=text name='password1' class="clear1" value="<?=$password1;?>" required="required">&nbsp;&nbsp;
            <input type=submit class="btn btn-primary">
            <button type="button" id="btnclear1" class="btn btn-warning">Очистить</button>
          </div>
        </form>
        <hr>
        <form action="client.php" method="post">
          <div style='display:inline:block' >
            id юр.лица:
            <input type=text name='jrlid2' class="clear2" value="<?=$jrlid2;?>" required="required">&nbsp;&nbsp;
            код производителя:
            <input type=text name='tovnum2' class="clear2" value="<?=$tovnum2;?>" required="required">&nbsp;&nbsp;
            бренд:
            <input type=text name='brand' class="clear2" value="<?=$brand;?>" required="required">&nbsp;&nbsp;
            кросс:
            <input type="checkbox" name='cross2' id="chk2" value="1" <?php if($cross2==1) echo'checked'; ?>>&nbsp;&nbsp;
            логин:
            <input type=text name='login2' class="clear2" value="<?=$login2;?>" required="required">&nbsp;&nbsp;
            пароль:
            <input type=text name='password2' class="clear2" value="<?=$password2;?>" required="required">&nbsp;&nbsp;
            <input type=submit class="btn btn-primary">
            <button type="button" id="btnclear2" class="btn btn-warning">Очистить</button>
          </div>
        </form>
        <hr>
        <form action="client.php" method="post">
          <div style='display:inline:block'>
            id юр.лица:
            <input type=text name='jrlid3' class="clear3" value="<?=$jrlid3;?>" required="required">&nbsp;&nbsp;
            нашему ключу:
            <input type=text name='keytov' class="clear3" value="<?=$keytov;?>" required="required">&nbsp;&nbsp;
            кросс:
            <input type="checkbox" name='cross3' id="chk3" value="1" <?php if($cross3==1) echo'checked'; ?>>&nbsp;&nbsp;
            логин:
            <input type=text name='login3' class="clear3" value="<?=$login3;?>" required="required">&nbsp;&nbsp;
            пароль:
            <input type=text name='password3' class="clear3" value="<?=$password3;?>" required="required">&nbsp;&nbsp;
            <input type="submit" class="btn btn-primary">
            <button type="button" id="btnclear3" class="btn btn-warning">Очистить</button>
          </div>
        </form>
        <hr>
    <?php

    require_once('lib/nusoap.php');
    $client = new nusoap_client('http://test2.itrade.forum-auto.ru/nusoap.local/service.php?wsdl');

              if(!empty($tovnum1)){
                  $result = $client->call("fa.getTov",['jrlid'=>$jrlid1, 'tovnum'=>$tovnum1, 'cross1'=>$cross1, 'login'=>$login1, 'password'=>$password1]);
                  if($result == "Неправильный id юр.лица или логин или пароль!"){
                  echo '<pre>'.$result.'</pre>';
                  }
                  else{
                  echo '<pre>';
                  var_dump($result);
                  echo '</pre>';

                    if(!empty($result)){
                      echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                    }
                    else{
                      echo 'по вашему запросу ничего не найдено!';
                    }
                  }

              }
              // по коду производителя и по бренду
              if(!empty($tovnum2) && !empty($brand)){
                  $result = $client->call("fa.getTov1",['jrlid'=>$jrlid2, 'tovnum'=>$tovnum2, 'brand'=>$brand, 'cross2'=>$cross2, 'login'=>$login2, 'password'=>$password2]);
                  if($result == "Неправильный id юр.лица или логин или пароль!"){
                  echo '<pre>'.$result.'</pre>';
                  }
                  else{
                    echo '<pre>';
                    var_dump($result);
                    echo '</pre>';

                    if(!empty($result)){
                      echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                    }
                    else{
                      echo 'по вашему запросу ничего не найдено!';
                    }
                  }
                }

              // по нашему ключу
              if(!empty($keytov)){
                  $result = $client->call("fa.getTov2",['jrlid'=>$jrlid3, 'keytov'=>$keytov, 'cross3'=>$cross3, 'login'=>$login3, 'password'=>$password3]);
                  if($result == "Неправильный id юр.лица или логин или пароль!"){
                  echo '<pre>'.$result.'</pre>';
                  }
                  else{
                    echo '<pre>';
                    var_dump($result);
                    echo '</pre>';

                    if(!empty($result)){
                      echo '<pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
                    }
                    else{
                      echo 'по вашему запросу ничего не найдено!';
                    }
                  }
              }


              // Отображаем запрос и ответ
              // echo '<h2>Запрос</h2>';
              // echo '<pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
              // echo '<h2>Ответ</h2>';
              //echo '<pre>' . htmlspecialchars($result, ENT_QUOTES) . '</pre>';
              // echo '<h2>Debug</h2>';
              // echo '<pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

    ?>
</div>
<script type="text/javascript">
   $(document).ready(function () {
     $("#btnclear1").click(function(e) {
        $('.clear1').val('');
        $('#chk1').removeAttr('checked');
     });
     $("#btnclear2").click(function(e) {
        $('.clear2').val('');
        $('#chk2').removeAttr('checked');
     });
     $("#btnclear3").click(function(e) {
        $('.clear3').val('');
        $('#chk3').removeAttr('checked');
     });
   });
</script>
</body>
</html>
