<?php

function replaceSpecial( $text ){
    // These mappings are from a unicode code point to zero or more other characters
    $unicodeMappings = array(
        '0080' => 'EUR',
        '0085' => ' ',
        '00B7' => ' ',
        '0091' => '\'',  // Left single quote
        '0092' => '\'',  // Right single quote
        '0093' => '"',   // Left double quote
        '0094' => '"',   // Right double quote
        '0096' => '-',   // Some kind of dash/hyphen/minus
        '0097' => '-',   // Some kind of dash/hyphen/minus
        '0099' => ' ',   // Trademark Symbol
        '00A0' => ' ',   // nbsp
        '00A3' => 'GBP',
        '00A5' => 'YEN',
        '00A9' => ' ',   // Copyright Symbol
        '00AE' => ' ',   // Registered Symbol
        '00B7' => ' ',
        '00C2' => ' ',
        '20AC' => 'EUR',
    );
    foreach( $unicodeMappings as $unicode => $replacement ){
        // A way to generate characters from their unicode code points 
        // is to json decode a string of the form "\uXXXX" (quotes included)
        $text = str_replace( json_decode( '"\u'.$unicode.'"' ), $replacement, $text );
    }
    return $text;
}

function stripHTMLTags(){
    $text = utf8_encode( $text );
    $text = preg_replace( '/<[^>]*>/', ' ', $text );
    $text = html_entity_decode( $text, ENT_COMPAT, 'UTF-8' );
    $text = replaceSpecial( $text );
    $text = preg_replace( '/\s+/', ' ', $text );
    return $text;
}


$res = array(
    'vars' => '',
    'text' => '',
);

if(!$_POST['url']){
    $res['vars'] = 'No URL';
    echo json_encode($res);
    exit;
}

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
    $stripped = stripHTMLTag($content);
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
