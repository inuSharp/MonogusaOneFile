<?php

/*
php -S 0.0.0.0:80
http://localhost
 */

ini_set('date.timezone', 'Asia/Tokyo');
mb_internal_encoding("UTF-8");

$consts = [
    'DEVELOP_MODE' => true,
    'NO_CACHE'     => true,
];

// css #################################################################################################################################################
$STYLE = <<<'STYLE'
<style>
</style>
STYLE;

// html #################################################################################################################################################
// header
$HEADER = <<<'HTML'
<header>
  <div><h1 style="display:inline-block;">API Client</h1></div>
</header>
HTML;

// pages
$VIEW = [];
$VIEW['index'] = <<<'HTML'
<div class="main">
  <h2 class="page-header">Result</h2>
  <div id="messages"></div>
</div>
HTML;

// javascript ###########################################################################################################################################
$SCRIPT = <<<'SCRIPT'
<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>

<script>
var SERVER = '@(WEB_ROOT)';
var _TOKEN = '@(TOKEN)';

function message(text) {
  document.getElementById('messages').innerHTML += text + '<br>';
}

$(function(){
  // GET
  $.ajax({
    url: "http://localhost/api",
    type: "GET"
  }).done(function (json) {
    console.log(json);
    message(json.message);
  }).fail(function (jqXHR) {
    if (jqXHR.status == 400) {
    } else if (jqXHR.status == 404) {
    } else {
    }
  }).always(function () {
  });

  // POST
  $.ajax({
    url: "http://localhost/api",
    type: "POST"
  }).done(function (json) {
    console.log(json);
    message(json.message);
  }).fail(function (jqXHR) {
    if (jqXHR.status == 400) {
    } else if (jqXHR.status == 404) {
    } else {
    }
  }).always(function () {
  });

  // PUT
  $.ajax({
    url: "http://localhost/api",
    type: "PUT"
  }).done(function (json) {
    console.log(json);
    message(json.message);
  }).fail(function (jqXHR) {
    if (jqXHR.status == 400) {
    } else if (jqXHR.status == 404) {
    } else {
    }
  }).always(function () {
  });

  // DELETE
  $.ajax({
    url: "http://localhost/api",
    type: "DELETE"
  }).done(function (json) {
    console.log(json);
    message(json.message);
  }).fail(function (jqXHR) {
    if (jqXHR.status == 400) {
    } else if (jqXHR.status == 404) {
    } else {
    }
  }).always(function () {
  });
});

</script>
SCRIPT;

// server side
function routeSetting()
{
    // 第3引数は認証するかしないか。true or 省略ならログインが必要
    RtGET('/', 'PageController::index', false);
    RtGET('/index', 'PageController::index', false);
    RtGET('/api', 'TestController::get', false);
    RtPOST('/api', 'TestController::post', false);
    RtPUT('/api', 'TestController::put', false);
    RtDELETE('/api', 'TestController::destroy', false);
}

class PageController
{
    public function index()
    {
        return render();
    }
}
class TestController
{
    public function get()
    {
        return responseJson(['message'=>'get success']);
    }

    public function post()
    {
        return responseJson(['message'=>'post success']);
    }

    public function put()
    {
        return responseJson(['message'=>'put success']);
    }

    public function destroy()
    {
        return responseJson(['message'=>'delete success']);
    }
}

// 以下Framework
$BASE_VIEW = <<<'HTML'
<!doctype html><html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
  <title>API Client</title>
  <link rel="stylesheet" type="text/css" href="@(WEB_ROOT)/monogusa.css" />
@(STYLE)
</head>
<body>
@(HEADER)
@(VIEW)
</body>
@(SCRIPT)
</html>
HTML;
function render($viewName = 'index') {
    global $STYLE,$VIEW, $BASE_VIEW, $SCRIPT,$HEADER;
    $html = str_replace(['@(STYLE)', '@(HEADER)', '@(VIEW)', '@(SCRIPT)'], [$STYLE, $HEADER, $VIEW[$viewName], $SCRIPT], $BASE_VIEW);
    $html = str_replace(['@(WEB_ROOT)', '@(TOKEN)'], [WEB_ROOT, TOKEN], $html);
    echo $html;
}
class Log
{
    public static $logDir = '';
    public static $rotated  = false;
    public static function getFilePath()
    {
        if (self::$logDir == '') {
            self::$logDir = 'log';
        }
        if (!file_exists(self::$logDir)) {
            echo self::$logDir . "\n";
            if(mkdir(self::$logDir, 0777)){
                //作成したディレクトリのパーミッションを確実に変更
                chmod(self::$logDir, 0777);
            }
        }
        return self::$logDir . '/' . date('Y-m-d') . '.log';
    }
    public static function access($s)
    {
        self::write('ACCESS', $s, self::getFilePath());
    }
    public static function info($s)
    {
        self::write('INFO', $s, self::getFilePath());
    }
    public static function error($s)
    {
        self::write('ERROR', $s, self::getFilePath());
    }
    public static function debug($s)
    {
        if (defined('DEBUG') && DEBUG == true) {
            self::write('DEBUG', $s, self::getFilePath());
        }
    }
    public static function write($tag, $s, $path)
    {
        self::rotate();
        if (is_array($s) || is_object($s)) {
            $s = json_encode($s);
        }
        $s = '[' . date('Y-m-d_H:i:s') . ']' . ' ' . $s;
        file_put_contents($path, $tag . ' : ' . $s . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    private static function rotate()
    {
        // 1リクエストで1回
        if (self::$rotated) {
            return;
        }
        self::$rotated = true;

        // 日をまたぐ間はろーてーとしない
        $nowTime = date('His');
        $time = intval(date('His'));
        // 0:05:00以下もしくは 23:55:00以上
        if ($time < 500 || 235500 < $time) {
            return;
        }

        foreach(glob(self::$logDir.'/*.log') as $file){
            $logdate = str_replace(['.log','-'], '', basename($file));
            // 今日のログならしない
            if ($logdate == date('Ymd')) {
                continue;
            }
            try {
                $ym      = substr($logdate,0,6);
                $backUpDir = self::$logDir.'/'.$ym;
                if (!file_exists($backUpDir)) {
                    if(mkdir($backUpDir, 0777)){
                        chmod($backUpDir, 0777);
                    }
                }
                rename($file, $backUpDir . '/' . basename($file));
            } catch(\Exception $e) {
                file_put_contents(dataDir(). 'log_error.txt', 'rotate error');
            }
        }
    }
}

ini_set( 'display_errors' , 0 );
error_reporting(E_ALL);
set_error_handler( 'my_error_handler', E_ALL );
register_shutdown_function('my_shutdown_handler');
function my_error_handler ( $errno, $errstr, $errfile, $errline, $errcontext ) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
function my_shutdown_handler(){
    $isError = false;
    if ($error = error_get_last()){
         switch($error['type']){
         case E_ERROR:
         case E_PARSE:
         case E_CORE_ERROR:
         case E_CORE_WARNING:
         case E_COMPILE_ERROR:
         case E_COMPILE_WARNING:
              $isError = true;
                     break;
             }
        }
    if ($isError) {
          $text = $error['type'] . '  ' . $error['message'] . '  ' .  
              $error['file'] . '  ' .  
              $error['line'];
          if (class_exists('Log')) {
              Log::error($text);
          } else {
              file_put_contents('exception.log',$text);
          }
    }
}
function responseJson($ary = [], $status = 200)
{
    header('Content-Type: application/json; charset=utf-8');
    if ($status != 200) {
        http_response_code($status);
    }
    if (is_array($ary) || is_object($ary)) {
        echo json_encode($ary);
    } else {
        echo $ary;
    }
}
function getHeader($name)
{
    $name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    }
    return false;
}
function Rt($method, $url, $callMethod = null, $authCheck = true) {
    static $routeMap;
    if (is_null($routeMap)) {
        $routeMap           = [];
        $routeMap['GET']    = [];
        $routeMap['POST']   = [];
        $routeMap['PUT']    = [];
        $routeMap['DELETE'] = [];
    }
    // ルート設定
    if (!is_null($callMethod)) {
        $routeMap[$method][$url] = [
            'method'    => $callMethod,
            'authCehck' => $authCheck,
        ];
        return;
    }
    // routing
    foreach ($routeMap[$method] as $key => $value) {
        $methosParam = [];
        $routeMatch = true;
        $route      = explode('/',ltrim($key, '/'));
        $requestUrl = explode('/',ltrim($url, '/'));
        if (count($route) != count($requestUrl)) {
            continue;
        }
        foreach ($route as $index => $routeName) {
            if (substr($routeName, 0, 1) == ':') {
                $methosParam[] = $requestUrl[$index];
            } else if ($routeName != $requestUrl[$index]) {
                $routeMatch = false;
                break;
            }
        }
        if ($routeMatch) {
            if ($value['authCehck']) {
                if (!authCheck()) {
                    header('Location: ' . WEB_ROOT . '/login');
                    exit();
                }
            }
            if (is_string($value['method'])) {
                $classInfo = explode('::', $value['method']);
                if (count($classInfo) == 2) {
                    //require_once 'server/Controllers/' . $classInfo[0] .'.php';
                    if (!class_exists($classInfo[0])) {
                        Log::error('ファイル名とクラス名が違います。');
                        throw new \Exception("class not defined.");
                    }
                    $routeClass = new $classInfo[0];
                    $methodName = $classInfo[1];
                    call_user_func_array([$routeClass, $methodName], $methosParam);
                    return;
                }
            } else {
                call_user_func_array($value['method'], $methosParam);
            }
            return;
        }
    }
    http_response_code(404);
    echo json_encode(['message' => 'Not Found!']);
}
function authCheck()
{
    return getSession('is_login', false);
}
function redirect($url)
{
    header('Location: ' . $url);
}
function RtGET($url, $callMethod = null, $authCheck= true)
{
    Rt('GET', $url, $callMethod, $authCheck);
}
function RtPOST($url, $callMethod = null, $authCheck= true)
{
    Rt('POST', $url, $callMethod, $authCheck);
}
function RtPUT($url, $callMethod = null, $authCheck= true)
{
    Rt('PUT', $url, $callMethod, $authCheck);
}
function RtDELETE($url, $callMethod = null, $authCheck= true)
{
    Rt('DELETE', $url, $callMethod, $authCheck);
}
function run()
{
    $route = str_replace(WEB_ROOT,'',REQUEST_URL);
    if ($route == '') {
        $route = '/';
    }
    $route = explode('?', $route)[0];
    header('Access-Control-Allow-Origin: *');
    if (REQUEST_METHOD == 'OPTIONS') {
        //header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Headers: Origin, Authorization, Accept, Content-Type, _token, firstOpenedAt");
            
        return;
    } else {
        //header("Access-Control-Allow-Origin: *");
    }
    request();
    Log::access(REQUEST_METHOD . ' '. REQUEST_URL . ' ' . clientIp());
    if (AUTH) {
        $token = md5(clientIp() . '_' . getHeader('firstOpenedAt'));
        if (getHeader('_token') != $token) {
            http_response_code(401);
            return;
        }
    }
    Rt(REQUEST_METHOD, $route);
}

function webInIt()
{
    define("TOKEN", 'jiw9hiohjdoiifhioi4ehjkjareqr7889uhgihs');
    // REQUEST_METHOD
    define("REQUEST_METHOD", $_SERVER["REQUEST_METHOD"]);

    $protocol = isset($_SERVER["https"]) ? 'https' : 'http';
    $domain =  $protocol . '://' . $_SERVER['HTTP_HOST'];
    // WEB_ROOT
    $subDir = '';
    define("WEB_ROOT", $domain. $subDir);
    // REQUEST_URL
    $requestUrl = $domain . $_SERVER['REQUEST_URI'];
    define("REQUEST_URL", $requestUrl);

    $url_path = ltrim(str_replace(WEB_ROOT, '', REQUEST_URL), '/');
    define("URL_PATH", $url_path);

    $route = ltrim(str_replace('//', '/', $url_path), '/');
    $route = preg_replace('/\?.*?$/', '', $route);
    if ($route === '') {
        $route = 'index';
    }
    $routes = explode('/', $route);
    if ($routes[0] == 'auth') {
        define("AUTH", true);
    } else {
        define("AUTH", false);
    }
}
function request($key = null)
{
    if (is_null($key)) {
        return null;
    }
    static $request;
    if (is_null($request)) {
        if ($_SERVER["REQUEST_METHOD"] == "PUT") {
            $putData = file_get_contents('php://input');
            $putData = explode("&", $putData);
            $request = [];
            foreach ($putData as $value) {
                $row = explode("=", $value);
                $request[$row[0]] = $row[1];
            }
        } else {
            $request = $_GET + $_POST;
        }
    }
    Log::info($request);
    
    return $request[$key];
}
function getRequestJson()
{
    static $json;
    if (is_null($json)) {
        $json = json_decode(file_get_contents('php://input'), true);
    }
    return $json;
}
function clientIp()
{
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}
function setSession($key, $value)
{
    $_SESSION[$key] = $value;
    if ($value === '') {
        $value = '<blank>';
    }
    if ($value === true) {
        $value = 'true';
    } else if ($value === false) {
        $value = 'false';
    }
    Log::debug("set session key:".$key."    value:".$value);
}
function getSession($key, $default = null)
{
    if (array_key_exists($key, $_SESSION)) {
        return $_SESSION[$key];
    } else {
        return $default;
    }
}
function setting($key)
{
    global $consts;
    if (!array_key_exists($key, $consts)) {
        throw new \Exception('key not exists.');
    }
    return $consts[$key];
}
function getFileLists($folder, $order = 0, &$maxCnt = -1, &$startCnt = 0, &$cnt = 0)
{
    $files = scandir($folder, $order);
    $lists = [];
    foreach($files as $file) {
        if ($file == ".." || $file == "." || $file == ".svn") {continue;}

        if (is_dir($folder.'/'.$file)) {
            $lists = array_merge($lists, getFileLists($folder.'/'.$file, $order, $maxCnt, $startCnt, $cnt));
        } else {
            $cnt++;
            if ($startCnt <= $cnt) {
                $lists[] = $folder.'/'.$file;
            }
        }
        if ($maxCnt != -1 && $maxCnt == $cnt) {
            break;
        }
    }
    return $lists;
}

function makeFolderIfNotExists($path)
{
    if (!file_exists($path)) {
        mkdir($path, '0777', true);
    }
}

function defaultCSS()
{
    if (file_exists('monogusa.css')) {
        return;
    }
    $css = <<<'STYLE'
/* tag */
* {
  padding:0px;
  margin:1px 0;
  font-family: 'Yu Gothic', 'Hiragino Kaku Gothic Pro', Meiryo, Osaka, 'MS PGothic', arial, helvetica, sans-serif;
  font-size: 19px;
  color: #505050;
}
html, body {height:100%;padding:0px;margin:0px;}
a { font-weight: bold; }
h1, h2, h3, h4, h5 { font-weight: normal; }

h1 { font-size:32px;} h2 { font-size:28px;} h3 { font-size:24px;} h4 { font-size:20px;}
header {
  margin:0 auto;
  width: calc(100% - 8px);
  max-width: 600px;
  margin-bottom:20px;
  padding: 0 4px;
}
header a {
  background-color: #fff;
  display: inline-block;
  text-align: center;
  min-width: 100px;
  background-color: #F8D6FF;
  color: #9C27B0;
  font-weight: normal;
  text-decoration: none;
  border-radius: 4px;
  padding: 3px;
}
pre, code, var, samp, kbd, .mono { font-family: Consolas, 'Courier New', Courier, Monaco, monospace; font-size: 14px;line-height: 1.3; }
pre { padding: 12px;overflow: auto; }
/* class */
.main {
  margin:0 auto;
  width: calc(100% - 8px);
  height: 100%;
  max-width: 600px;
  padding: 0 4px;
}
.form-text {
  -webkit-appearance: none;
  background-color: #fff;
  background-image: none;
  border-radius: 4px;
  border: 1px solid #dcdfe6;
  box-sizing: border-box;
  color: #606266;
  display: inline-block;
  height: 40px;
  line-height: 40px;
  outline: none;
  padding: 0 15px;
  transition: border-color .2s cubic-bezier(.645,.045,.355,1);
  width: 100%;
}
.btn {
  display: inline-block;
  padding: 0.3em 1em;
  text-decoration: none;
  border: solid 2px #aaa;
  border-radius: 3px;
  background: #fff;
  transition: .4s;
  cursor: pointer;
  outline: none;
}
.btn:hover {
    background: #eee;
}
.divider {
  border-top: 1px solid #CCC;
  margin-bottom: 0;
  margin-top: 24px;
  text-align: center;
}
.divider span {
  background: #fff;
  display: inline-block;
  padding: 0 24px;
  position: relative;
  font-size: 24px;
  top: -26px;
}
.page-header {
  border-bottom: 1px solid #F8D6FF;
  margin-bottom: 10px;
}
STYLE;

    file_put_contents('monogusa.css', $css);
}
try {
    session_name("7CXziwojoiiejqji899h84hcb8");
    session_start();
    defaultCSS();
    makeFolderIfNotExists('log');
    webInIt();
    routeSetting();
    run();
} catch (Exception $e) {
    Log::error($e->getMessage().'  '.$e->getFile().'('.$e->getLine().')');
    http_response_code(500);
    echo json_encode(['message'=>'inernal server error!']);
}

