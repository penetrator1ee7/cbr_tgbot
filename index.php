<?php
const token='hihiihihi';
const base='https://api.telegram.org/bot';
const path='/tgbot/index.php';
const server='https://123456879.ngrok.io';
const greet='Hello and thanks for using our bot.    Type "/date" followed by *dd/mm/yy* or "now/yesterday/last month etc." to set required date for currency.  Then type "/val" followed by currency name/charcode/numcode to get what you are here for)   You can always type "/help" for help';
/*$url=base.token.'/setWebhook?url='.server.path;       //!!!to start hook!!!
$response=file_get_contents($url);
echo $response;*/

$timeFlag=false;
$users=json_decode(file_get_contents('users.json'));

$newMsg=file_get_contents("php://input");
file_put_contents('D:\wamp64\www\tgbot\data.json',$newMsg); //to check response
$newMsg=json_decode($newMsg);


if(strpos($newMsg->message->text,'start')||strpos($newMsg->message->text,'help')){   //greet msg
    $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=' . greet;
    echo file_get_contents($sendMessage);

}elseif (strpos($newMsg->message->text,'date')){         //set the date for current user
    for($i=0;$i<count($users);$i++){                             //check if wrong date was already set
        if($users[$i]->user_id==$newMsg->message->chat->id){
           array_splice($users,$i,1);
        }
    }
    $users[]=array('user_id'=>$newMsg->message->chat->id,'date'=>substr($newMsg->message->text ,'5'));
    file_put_contents('users.json',json_encode($users));

    $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=Date was successfully set';
    echo file_get_contents($sendMessage);

}elseif (strpos($newMsg->message->text,'val')){      //bot response for currency according to previously set time modifying time for request and minding how the currency was set
    for($i=0;$i<count($users);$i++){
        if($users[$i]->user_id==$newMsg->message->chat->id){
            $time=$users[$i]->date;
            $timeFlag=true;
        }
    }
    if(!$timeFlag){
        $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=' .'You shall set the date for your currency check first.';
        echo file_get_contents($sendMessage);
    }
    $time=str_replace('/','-',$time);
    $time=strtotime($time);
    $time=date('d/m/Y',$time);
    $tmpmsg=substr($newMsg->message->text,'4');
    if(preg_match('/\d\d\d/',$tmpmsg)){
        $curFormat='NumCode';
    }elseif (preg_match('/\D\D\D/',$tmpmsg)&&strlen($tmpmsg)<5){
        $curFormat='CharCode';
    }else{
        $curFormat='Name';
    }
    $codeRequest='http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$time;
    $foundFlag=false;
    if($curFormat=='NumCode'){
        for($i=0;$i<count(simplexml_load_file($codeRequest)->Valute);$i++) {
            if(levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->$curFormat)<2){   /// val name request
                $price=simplexml_load_file($codeRequest)->Valute->$i->Value;
                $nominal=simplexml_load_file($codeRequest)->Valute->$i->Nominal;
                $foundFlag=true;
            }
        }
    }elseif($curFormat=='CharCode'){
        for($i=0;$i<count(simplexml_load_file($codeRequest)->Valute);$i++) {
            if(levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->$curFormat)<2){   /// val name request
                $price=simplexml_load_file($codeRequest)->Valute->$i->Value;
                $nominal=simplexml_load_file($codeRequest)->Valute->$i->Nominal;
                $foundFlag=true;
            }
        }
    }elseif ($curFormat=='Name'){
        for($i=0;$i<count(simplexml_load_file($codeRequest)->Valute);$i++) {
            if(levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->$curFormat)<4){   /// val name request
                $price=simplexml_load_file($codeRequest)->Valute->$i->Value;
                $nominal=simplexml_load_file($codeRequest)->Valute->$i->Nominal;
                $foundFlag=true;
            }
        }
    }
    if($foundFlag) {
        $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=' . 'For date '.$time.' it was sold '.$nominal.' of '.$tmpmsg.' for price '.$price;
        echo file_get_contents($sendMessage);
    }else{
        $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=' . 'We cannot find the currency you inserted(';
        echo file_get_contents($sendMessage);
    }

}else{
    $sendMessage = base . token . '/sendMessage?chat_id=' . $newMsg->message->chat->id . '&text=We do not understand what you are talking about(';
    echo file_get_contents($sendMessage);
}



/*
$foundFlag=false;
$newMsg=json_decode(file_get_contents('data.json'));
var_dump($newMsg->message->text);
$tmpmsg=substr($newMsg->message->text,'4');
$time=$users[0]->date;
$time=str_replace('/','-',$time);
$time=strtotime($time);
$time=date('d/m/Y',$time);
$tmpmsg='cash';
if(preg_match('/\d\d\d/',$tmpmsg)){
    $curFormat='NumCode';
}elseif (preg_match('/\D\D\D/',$tmpmsg)&&strlen($tmpmsg)<5){
    $curFormat='CharCode';
}else{
    $curFormat='Name';
}
echo $curFormat;

$codeRequest='http://www.cbr.ru/scripts/XML_daily.asp?date_req='.$time;
$curFormat='Name';
$i=0;
echo levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->$curFormat);

for($i=0;$i<count(simplexml_load_file($codeRequest)->Valute);$i++) {
    if (levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->$curFormat)<2) {   /// val name request
        $foundFlag = true;
    }
}
$i=0;
echo $foundFlag;

/*
//var_dump(simplexml_load_file($codeRequest));
echo $tmpmsg;
echo simplexml_load_file($codeRequest)->Valute->$i->CharCode;
echo levenshtein($tmpmsg,simplexml_load_file($codeRequest)->Valute->$i->CharCode);
echo $foundFlag;

$i=1;
array_splice($users,$i,1);
var_dump($users);
*/
