<?php
function singleElementParser($string){
	if( !preg_match('/^<(.*?)>$/', $string) ){
		return '';
	}
	$string = substr($string, 1, -1);

	//remove self close tag /
	$string = rtrim($string, '/');
	$postionWhitespace = strpos($string . ' ', ' ');
	$tag = substr($string, 0, $postionWhitespace);
	if(!$tag){
		return '';
	}
	$tag = strtolower($tag);
	$isCloseTag = substr($tag, 0, 1) === '/';

	$string = substr($string, $postionWhitespace);
	$string = trim($string);

	$attrs = array();
	while(!$isCloseTag && $string){
		$string = trim($string);

		//find attributeName
		$attrNameEndPostion = min(
			strpos($string . '=', '='), 
			strpos($string . ' ', ' ')
			);


		$key = substr($string, 0, $attrNameEndPostion);
		$key = strtolower($key);
		$string = trim(substr($string, $attrNameEndPostion));
		if(substr($string.' ', 0, 1) !== '='){
			$value = NULL;
		}else{
			$string = substr($string, 1);
			$string = trim($string);
			$firstCharacter = substr($string, 0, 1);
			if($firstCharacter === '"' || $firstCharacter === '\''){
				$posStart = 1;
				$posEnd = strpos($string, $firstCharacter, 1);
			}else{
				$posStart = 0;
				$posEnd = strpos($string . ' ', ' ');
			}

			// broken attribute value
			// we can break the loop or try to fix it
			if( $posEnd === FALSE ){
				break;
				//$posEnd = strlen($string);
			}

			$value = substr($string, $posStart, $posEnd - $posStart );
			$string = trim(substr($string, $posEnd + $posStart));
		}

		$attrs[$key] = $value;
	}

	$attrArray = array();
	foreach($attrs as $key => $value){
		$deafultAttributes = array(
			'script' => array(
				'type' => 'text/javascript',
			),
			'style' => array(
				'type' => 'text/css',
			),
		);

		if(isset($deafultAttributes[$tag]) 
			&& isset($deafultAttributes[$tag][$key])
			&& $deafultAttributes[$tag][$key] === $value){
			continue;
			//unset($attrs[$attrs]);
		}

		if( is_null($value) ){
			$attrArray[] = $key;
		}elseif( strpos($value, '"') !== FALSE ){
			$attrArray[] = $key . '=\'' . $value . '\'';
		}else{
			$attrArray[] = $key . '="' . $value . '"';
		}
	}


					
	return '<' . $tag . (empty($attrArray) ? '' : ' ' . implode(' ', $attrArray)) . '>';
}




function compress_html($html){
	// a guid to avoid mistake replace
	$guid = md5(time() . rand());

	// cache special tag
	$special_tags = array(
		'code',
		'pre',
		'script',
		'style',
		'textarea',
	);

	$cache_special_tags_content = array();
	foreach($special_tags as $tag){
		if( preg_match_all('/<' . $tag . '(?:[^>]*?)>(.*?)<\/' . $tag . '>/is', $html, $matches) ){
			$cache_special_tags_content[$tag] = $matches[0];
			foreach($matches[0] as $index => $string){
				$html = str_replace( $string , '<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~' . $tag .'~' . $index . '~~>', $html );
			}
		}
	}


	// CDATA
	if( preg_match_all('/<!\[CDATA\[(.*?)\]\]>/s', $html, $matches) ){
		$cache_special_tags_content['CDATA'] = $matches[0];
		foreach($matches[0] as $index => $string){
			$html = str_replace( $string , '<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~CDATA~' . $index . '~~>', $html );
		}
	}

	// remove comment tag
	$html = preg_replace('/<!--[^\[].*?-->/s', '', $html);

	// replace all space characters to U+0020 SPACE,
	// "tab" (U+0009), "LF" (U+000A), "FF" (U+000C), and "CR" (U+000D).
	$html = preg_replace('/\t|\n|\f|\r/', ' ', $html);


	// get all tags
	if(preg_match_all('/<[^!][^>]+>/', $html, $matches)){
		$pieces = $matches[0];
		$pieces = array_unique($pieces);
		//sort($pieces);

		$fixedPieces = array();
		foreach($pieces as $original){
			$fixed = singleElementParser($original);

			if($fixed != $original){
				$fixedPieces[$original] = $fixed;
			}
		}

		// TODO :
		// Certain tags can be omitted, see at
		// http://www.w3.org/TR/html5/single-page.html#syntax-attributes
		// section 8.1.2.4 Optional tags


		$html = strtr($html, $fixedPieces);

	}

	// remove whitespace
	$html = trim($html);
	$html = preg_replace('/\s+/', ' ', $html);

	// remove whitespace between tags
	$html = str_replace('> <', '><', $html);

	// retore special tag
	// TODO: remove new line after the special tag
	while(preg_match('/<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~/', $html)){
		foreach( $cache_special_tags_content as $tag => $content ){
			foreach( $content as $index => $string ){
				$string = preg_replace_callback(
					'/^(<' . $tag . '(?:[^>]*?)>)(.*?)(<\/' . $tag . '>)$/is',
					function($matches){
						return singleElementParser($matches[1]) . trim($matches[2]) . singleElementParser($matches[3]);
					}, 
					$string);
				//$string = trim($string, "\r\n\t");
				$html = str_replace( '<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~' . $tag .'~' . $index . '~~>', $string, $html );
			}
		}
	}

	return $html;
}