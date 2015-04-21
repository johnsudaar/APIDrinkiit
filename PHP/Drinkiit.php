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

  public static $ORDERS_ALL = 0;
  public static $ORDERS_SERVED = 1;
  public static $ORDERS_PENDING = 2;

  private $email;
  private $pass;
  private $token;
  private $last_error;

  static function configure($url){
    Drinkiit::$DRINKIIT_URL = $url;
  }

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

  function getMeals(){
    $response = httpGet(Drinkiit::$DRINKIIT_URL."/menu");
    $data = json_decode($response);
    if($data->type === "error")
      return array();
    else{
      $result = array();

      foreach($data->data as $meal){

        $result[str_replace(" ","",strtolower($meal->name))] = array("id"=>$meal->id,"description"=>$meal->description,"price"=>$meal->price);
      }
      return $result;
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

  function getOrders($filter){
    if($filter != Drinkiit::$ORDERS_SERVED and $filter != Drinkiit::$ORDERS_PENDING){
      $filter = Drinkiit::$ORDERS_ALL;
    }
    if(! $token= $this->getToken()){
      return false;
    }
    $response = httpGet(Drinkiit::$DRINKIIT_URL."/orders",array("token"=>$token));
    $data = json_decode($response);
    if($data->type === "error"){
      $this->last_error = $data->message;
      return false;
    }

    $orders = array();

    foreach($data->data as $cur){
      if($filter == Drinkiit::$ORDERS_ALL || ($filter == Drinkiit::$ORDERS_SERVED && $cur->done) || ($filter == Drinkiit::$ORDERS_PENDING && ! $cur->done) )
      $orders[] = array("date"=>$cur->date,"price"=>$cur->total,"done"=>$cur->done, "name"=>$cur->content[0]->name, "comment"=> $cur->content[0]->comment, "quantity"=>$cur->content[0]->quantity);
    }
    return $orders;
  }

  function getUserInfo(){
    if(! $token = $this->getToken()){
      return false;
    }
    $response = httpGet(Drinkiit::$DRINKIIT_URL."/userInfo",array("token"=>$token));
    $data = json_decode($response);
    if($data->type === "error"){
      $this->last_error = $data->message;
      return false;
    }
    return $data->data;
  }

  function getLastError(){
    return $this->last_error;
  }

}
