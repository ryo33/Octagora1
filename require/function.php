<?php

function h($text){
    return htmlspecialchars($text, ENT_QUOTES, 'EUC-JP');
}

function stremp($text){
    if(is_array($text)){
        foreach($text as $t){
            if(stremp($t)){
                return true;
            }
        }
        return false;
    }else{
        return $text === null || strlen($text) === 0;
    }
}

function check_request($arg){
    if(stremp($arg)){
        return false;
    }
    return true;
}

function tag($text, $tag = 'p'){
    return '<' . $tag . '>' . h($text) . '</' . $tag . '>' . "\n";
}

function debug($text){
    echo tag($text);
}

function echoh($text){
    echo h($text);
}

function redirect($url){
    header("Location: " . $url);
    exit();
}

function get_token($form_name){
    $key = 'csrf_tokens/' . $form_name;
    $tokens = isset($_SESSION[$key]) ? $_SESSION[$key] : array();
    if(count($tokens) >= 10){
        array_shift($tokens);
    }
    $token = sha1($form_name . session_id() . microtime());
    $tokens[] = $token;

    $_SESSION[$key]=$tokens;

    return sha256($token);
}

function check_token($form_name, $token){

    $key = 'csrf_tokens/' . $form_name;
    $tokens = isset($_SESSION[$key]) ? $_SESSION[$key] : array();

    if(false !== ($pos = array_search($token, $tokens, true))){
        unset($tokens[$pos]);
        $_SESSION[$key] = sha1($tokens);
        return true;
    }
    return false;
}

function sha256($target) {
    return hash('sha256', $target);
}

function now($format=false, $option = null){
    if($option === null){
        $datetime = new DateTime('now',new DateTimeZone('GMT'));
        return $format?$datetime->format('U'):$datetime;
    }else{
        $datetime = new DateTime($option,new DateTimeZone('GMT'));
        return $format?$datetime->format('U'):$datetime;
    }
}

function delete_null_byte($value){
    if(is_string($value) === true){
        $value = str_replace("\0","",$value);
    }else if(is_array($value) === true){
        $value = array_map('delete_null_byte',$value);
    }
    return $value;
}