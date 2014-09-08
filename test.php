<?php
date_default_timezone_set("Asia/Shanghai");
mb_internal_encoding("UTF-8");
require_once('html_compresser.php');
//ob_start('compress_html');

$demos = array();

//$html = file_get_contents('http://www.163.com');
//$html = iconv('gbk','utf-8',$html);
//print_r(compress_html($html));
//exit;

	$urls = array(
		array('http://www.baidu.com/'),
		['http://www.baidu.com/s?wd=fisker'],
		['http://www.baidu.com/s?wd=html%20compresser'],
		['https://github.com/fisker/php-html5-compresser'],
		//['http://www.163.com/','gbk'],
		['http://www.taobao.com/','gbk'],
	);

$s = <<<eot
	<a 
        data-click="{
		'F':'778717EA',
		'F1':'9D73F1E4',
		'F2':'4CA6DE6B',
		'F3':'54E5243F',
		'T':'1410194465',
		'y':'1DFABDDF'
		}"
    	href = "//www.baidu.com/link?url=BPIG2w8q78UPekDLIk6LEpuRrArgSYgO1ZMMdU_A-XaQ6wnElZ-xmSRGgCxZK6SywtofED3q_52G6xbNk7SBxK"
		target="_blank" 		
	>DB文件打包器DB<em>Compressor</em>(DB<em>Compresser</em>) - 绿色软件联盟 - 《绿...</a>
eot;
$demos[] = array(
	'value of attribute is in multiple lines(this code was found in baidu search result page)',
	$s
);


$s = <<<eot
<script>
(function($){
	$(document).ready(function(){
		alert('hello world.');
		alert('another line with special character <>\'"&nbsp;.');
	});
})(jQuery);
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

$s = <<<eot
<script type="text/javascript">
    var fisker = 'jerk';//this is a javescript comment
</script>
eot;


$demos[] = array(
	'scripts with comment',
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
	'attribute and value contains whitespace',
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
	'attributes with boolean type value',
	'<video controls="controls"><video controls=controls><video controls><video controls="true"><video controls="false"><input checked="whatever">',
);

$demos[] = array(
	' typo attributes',
	'<a href = http://google.com">test</a>',
);

$demos[] = array(
	' broken attributes',
	'<a href="google>',
);
$demos[] = array(
	' broken attributes2',
	'<a href="google\'>',
);

$demos[] = array(
	'empty element',
	'<>',
);


$demos[] = array(
	'multiple lines(new version will remove whitespace)',
	"\t<title>\n\t\tthis is a title\n\t</title>",
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>
	a php compresser to compress html
</title>
<link href="http://cdn.staticfile.org/twitter-bootstrap/3.2.0/css/bootstrap.min.css" rel="stylesheet">

<script src="http://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
<script src="http://cdn.staticfile.org/prettify/r298/prettify.min.js"></script>

<script>
	$(document).ready(prettyPrint);
</script>
<style>
.com { color: #93a1a1; }
.lit { color: #195f91; }
.pun, .opn, .clo { color: #93a1a1; }
.fun { color: #dc322f; }
.str, .atv { color: #D14; }
.kwd, .linenums .tag { color: #1e347b; }
.typ, .atn, .dec, .var { color: teal; }
.pln { color: #48484c; }

.prettyprint {
padding: 8px;
background-color: #f7f7f9;
border: 1px solid #e1e1e8;
}
.prettyprint.linenums {
-webkit-box-shadow: inset 40px 0 0 #fbfbfc, inset 41px 0 0 #ececf0;
-moz-box-shadow: inset 40px 0 0 #fbfbfc, inset 41px 0 0 #ececf0;
box-shadow: inset 40px 0 0 #fbfbfc, inset 41px 0 0 #ececf0;
}

/* Specify class=linenums on a pre to get line numbering */
ol.linenums li {
padding-left: 12px;
color: #bebec5;
line-height: 18px;
text-shadow: 0 1px 0 #fff;
}
</style>

</head>
<body>
<?if(isset($_GET['real'])){?>
	<?

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
		<pre class="prettyprint linenums"><code class="lang-html"><?=htmlspecialchars($code)?></code></pre>
	</div>
	<div class="col-lg-6 col-md-6 col-sm-6 col-sx-6">
		<pre class="prettyprint linenums"><code class="lang-html"><?=htmlspecialchars($code2)?></code></pre>
	</div>
	</div>
<?}else{?>
	<div class="container">
	<?foreach($demos as $s){?>
	<div class="panel panel-default">
	<div class="panel-heading">
	<?=$s[0]?>
	</div>
	<div class="panel-body">
		<pre class="prettyprint linenums"><code class="lang-html"><?=htmlspecialchars($s[1])?></code></pre>
		<pre class="prettyprint linenums"><code class="lang-html"><?=htmlspecialchars(compress_html($s[1]))?></code></pre>
	</div>
	</div>
	<?}?>

	<a href="?real" class="btn btn-primary btn-lg btn-block">realtime page example</a>
	</div>
<?}?>


</body>
</html>