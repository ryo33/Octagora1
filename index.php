<?php
$microtime = (int) (microtime(true) * 1000);
$now = new DateTime('now', new DateTimeZone('GMT'));

mb_internal_encoding('UTF-8');
mb_http_input('auto');
mb_http_output('UTF-8');

define('DIR', dirname(__FILE__) . '/');
define('REQ', DIR . 'require/');
define('URL_HEAD', empty($_SERVER['HTTPS']) ? 'http://' : 'https://');
define('URL_BODY', $_SERVER["HTTP_HOST"] . '/');
define('URL', URL_HEAD . URL_BODY);

require DIR . 'start.php';
require REQ . 'function.php';
require REQ . 'function2.php';
require DIR . 'setting.php';
require REQ . 'ClassLoader.php';

$loader = new ClassLoader();
$loader->register_directory(REQ);
$loader->register();

define('LAST', 'l');
define('START', 's');
define('LENGTH', 'm');
define('TAGS', 'ts');
define('TAGS_OPTION', 'tn');
define('TEXT', 't');
define('NEEDS', 'n');
define('ORDER', 'o');
define('CLIENT_ID', 'client_id');
define('CLIENT_SECRET', 'client_secret');
define('ACCESS_TOKEN', 'access_token');
define('ERROR', 'error');

define('ACTION', 'action');
define('TOKEN', '_Token');

define('AUTO_LOGIN', 'remember');
define('DEBUG', false);

define('FORM_TAGS', 'form_tags');
define('POST_TAGS', 'post_tags');

$con = new EasySql($database_dsn, $database_username, $database_password);
$req = new Request();
$res = new Response();
$tmpl = new Template();
$se = new Session();
$user = new User($con);

if(DEBUG){
    $con->debug(true);
}

try{
    ob_start();

    login();

    if($se->check_login($user)){
        $tmpl->add_navbar(Design::tag('li', Design::link('users', 'User Page')));
    }else{
        $tmpl->add_navbar(Design::tag('li', Design::link('users', 'Login')));
    }

    switch($req->get_uri()){
    case 'api':
        $is_api = true;
        require DIR . 'api/index.php';
        break;
    case 'oauth':
        require DIR . 'oauth/index.php';
        break;
    default:
        $req->get_uri(0, -1);
        $is_api = false;
        require DIR . 'web/index.php';
        break;
    }
    quit();
}catch(Exception $e){
    error_log($e->getMessage());
    error(500, 'error', $e->getMessage());
}

function quit($error=false){
    global $req, $res, $is_api;
    if($is_api && $error === false){
        if($req->check_param() === true){
            error(400, 'unused parameter');
        }
        if($req->check_uri()){
            error(400, 'uri');
        }
    }
    if($is_api){
        header('Content-Type: application/json; charset=utf-8');
    }
    $res->display();
    ob_end_flush();
    exit();
}

function error($status, $message, $log=false){
    global $con, $res, $req, $is_api;
    if($is_api){
        $error = $req->get_param(ERROR, false);
        if($error === '200'){
            http_response_code(200);
        }else{
            http_response_code($status);
        }
        $json['status'] = "$status";
        $json['error'] = "$message";
        $res->content = [json_encode($json)];
    }else{
        redirect('?reload=true');
    }
    if($log !== false || !$is_api || DEBUG){
        $con->insert('error_log', array('status', 'message', 'text', 'created'), array($status, $message, $log, now()->format('Y:m:d H:i:s')));
    }
    quit(true);
}
