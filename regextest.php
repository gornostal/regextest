<?php

$res = array(
    'vars' => '',
    'text' => '',
);

if(!$_POST['url']){
    $res['vars'] = 'No URL';
    echo json_encode($res);
    exit;
}
require 'stringUtils.php';

$dir = '/tmp/stripped-html';
if( !file_exists($dir) ){
    mkdir($dir, 0777);
}

$file = $dir.'/'.md5($_POST['url']);
if(file_exists($file)) {
    $stripped = file_get_contents($file);
} else {
    $content = file_get_contents($_POST['url']);
    if(!$content){
        $res['vars'] = 'No content';
        echo json_encode($res);
        exit;
    }
    $stripped = StrUtils::stripHTMLTag($content);
    file_put_contents($file, $stripped);
}
$res['text'] = $stripped;

if(!$_POST['regex']){
    $res['vars'] = 'No regex';
    echo json_encode($res);
    exit;
}
$regex = '/('.str_replace("\n", '', $_POST['regex']).')/'.$_POST['modifiers'];


if( preg_match_all($regex, $stripped, $matches, PREG_SET_ORDER) ) {
    foreach ($matches as $key => $match) {
        foreach ($match as $j => $value) {
            if(is_numeric($j)){
                unset($matches[$key][$j]);
            }
        }
    }

    ob_start();
    print_r($matches);
    $vars = ob_get_contents();
    ob_end_clean();
    $res['vars'] = $vars;

    $res['text'] = preg_replace($regex, '<span class="hl">$1</span>', $stripped);

    echo json_encode($res);
}
