<?php
date_default_timezone_set("Asia/Shanghai");
mb_internal_encoding("UTF-8");
require_once('html_compresser.php');
ob_start('compress_html');

$demos = array();

//$html = file_get_contents('http://www.163.com');
//$html = iconv('gbk','utf-8',$html);
//print_r(compress_html($html));
//exit;



$s = <<<eot
<script>
(function($){
	$(document).ready(function(){
		alert('hello world.');
		alert('another line with special character <>\'"&nbsp;.');
	});
})(jQUery);
</script>
eot;
$demos[] = array(
	'inline pre code text area CDATA',
	$s
);

$s = <<<eot
<!--
this is a comment, this will be gone
-->

<!--[if IE]> this will stay <![endif]-->
eot;

$demos[] = array(
	'remove comment',
	$s
);


$demos[] = array(
	'whitespace',
	"\\r:\r , \\n: \n, \\t : \t, \\f : \f, whitespace\r\n\t\f &nbsp; \n a long white space'                 '"
);

$demos[] = array(
	'useful whitespace',
	'fisker &nbsp; cheung<a href="">my homepage</a>&nbsp;<img src="path"> <a href="">another without a space before this element</a>'
);

$demos[] = array(
	'attribute and value contains whitespace (will be fixed next version)',
	'<a href = "http://google.com">test</a>'
);


$demos[] = array(
	'upper case HTML tagname or attribute name',
	'<A href="google" TITLe="GOOgle">'
);

$demos[] = array(
	'self-closing tag',
	'<IMG src="path/to/" /><IMG src="path/to/"/><IMG src="path/to/">'
);

$demos[] = array(
	'close tag clean up',
	'</html></html ></html  ???></html something?>'
);
$demos[] = array(
	'attribute in different style',
	'<button disabled="disabled"> <button disabled=\'disabled\'><button disabled=disabled><button disabled >'
);


$demos[] = array(
	'a button that onclick attribute is Single-quoted and there is a Double-quote mark in the value',
	'<button onclick=\'javascript:alert("this links to google")\'>',
);


$demos[] = array(
	'attributes with useless value',
	'<script type=\'text/javascript\' >',
);

$demos[] = array(
	' broken attributes',
	'<a href="google>',
);
$demos[] = array(
	' broken attributes2',
	'<a href="google\'>',
);


?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>a php compresser to compress html</title>
<link href="http://cdn.staticfile.org/twitter-bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">
<link href="http://getbootstrap.com/assets/css/docs.min.css" rel="stylesheet">

<script src="http://cdn.staticfile.org/prettify/r298/run_prettify.min.js"></script>


</head>
<body>

<div class="container">
<?foreach($demos as $s){?>
<div class="panel panel-default">
<div class="panel-heading">
<?=$s[0]?>
</div>
<div class="panel-body">
	<pre class="prettyprint">
	<code class="lang-html html"><?=htmlspecialchars($s[1])?></code>
	</pre>
	<pre class="prettyprint">
	<code class="lang-html html"><?=htmlspecialchars(compress_html($s[1]))?></code>
	</pre>
</div>
</div>
<?}?>




</div>

<?
$urls = array(
	['http://www.baidu.com/'],
	['http://www.baidu.com/s?wd=fisker'],
	['http://www.baidu.com/s?wd=html%20compresser'],
	['https://github.com/fisker/php-html5-compresser'],
	//['http://www.163.com/','gbk'],
	['http://www.taobao.com/','gbk'],
);
$url = $urls[rand(0,count($urls)-1)];
$c = isset($url[1]) ? $url[1] : '';
$url = $url[0];
$code = file_get_contents($url);
if($c){
	$code = iconv($c,'utf-8',file_get_contents($url));
}
$code2 = compress_html($code);
?>

<div class="container">
<h1>a real time page <?=$url?></h1>
<p class="well">
	original length : <?=strlen($code)?> , compressed length : <?=strlen($code2)?>, saved : <?=ceil((strlen($code)-strlen($code2))/strlen($code) * 10000)/100?>%

</p>
<div class="col-lg-6 col-md-6 col-sm-6 col-sx-6">
	<pre class="prettyprint">
	<code class="lang-html html"><?=htmlspecialchars($code)?></code>
	</pre>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 col-sx-6">
	<pre class="prettyprint">
	<code class="lang-html html"><?=htmlspecialchars($code2)?></code>
	</pre>
</div>
</div>

</body>
</html>