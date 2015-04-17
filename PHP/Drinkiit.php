<?php

// Trouvé sur : http://hayageek.com/php-curl-post-get/

function httpPost($url,$params)
{
  $postData = '';
  //create name value pairs seperated by &
  foreach($params as $k => $v)
  {
    $postData .= $k . '='.$v.'&';
  }
  rtrim($postData, '&');
  $ch = curl_init();

  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
  curl_setopt($ch,CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_POST, count($postData));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

  $output=curl_exec($ch);
  curl_close($ch);
  return $output;

}


function httpGet($url,$params = array())
{
    $ch = curl_init();
    $data = count($params) > 0 ? '?' : '';
    foreach($params as $k => $v){
      $data .= $k."=".$v.'&';
    }
    curl_setopt($ch,CURLOPT_URL,$url.$data);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $output=curl_exec($ch);

    curl_close($ch);
    return $output;
}


class Drinkiit{
  private static $DRINKIIT_URL = "https://drinkiit.fr/api/";

  private $email;
  private $pass;
  private $token;
  private $last_error;

  function __construct($email, $pass){
    $this->email = $email;
    $this->pass = $pass;
    $this->token = "";
  }

  function getToken(){
    if($this->token == ""){
      return $this->_getToken();
    }else if(Drinkiit::isTokenValid($this->token)){
      return $this->token;
    }else{
      return $this->_getToken();
    }
  }

  static function isTokenValid($token){
    $response = httpGet(Drinkiit::$DRINKIIT_URL."/isKeyValid",array("token"=>$token));
    $decoded  = json_decode($response);
    return $decoded->value;
  }

  function _getToken(){
    $response = httpPost(Drinkiit::$DRINKIIT_URL."/token", array("email"=>$this->email,"password"=>$this->pass));
    $decoded  = json_decode($response);
    if($decoded->type === "error"){
      $this->last_error = $decoded->message;
      return false;
    }else{
      return $decoded->data;
    }
  }

  function order($meal_id, $count, $comment){
    $token = $this->getToken();
    $data = array("token"=>$token,"meal_id"=>$meal_id,"qty"=>$count,"comment"=>$comment);
    $response = httpPost(Drinkiit::$DRINKIIT_URL."/order",$data);
    $decoded  = json_decode($response);
    if($decoded->type === "error"){
      $this->last_error = $decoded->message;
      return false;
    }else{
      return true;
    }
  }

  function getLastError(){
    return $this->last_error;
  }

}
