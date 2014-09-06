<?php
function compress_html($html){
	// a guid to avoid mistake replace
	$guid = md5(time() . rand());

	// cache special tag
	$special_tags = array(
		'pre',
		'code',
		'script',
		'style',
		'textarea',
	);

	$cache_special_tags_content = array();
	foreach($special_tags as $tag){
		if( preg_match_all('/<' . $tag . '(?:[^>]*?)>(.*?)<\/' . $tag . '>/is', $html, $matches) ){
			$cache_special_tags_content[$tag] = $matches[1];
			foreach($matches[1] as $index => $string){
				$html = str_replace( $string , '[%~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~' . $tag .'~' . $index . '%]', $html );
			}

		}
	}

	// CDATA
	if( preg_match_all('/<!\[CDATA\[(.*?)\]\]>/s', $html, $matches) ){
		$cache_special_tags_content['CDATA'] = $matches[1];
		foreach($matches[1] as $index => $string){
			$html = str_replace( $string , '[%~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~CDATA~' . $index . '%]', $html );
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
		sort($pieces);

		$fixedPieces = array();
		foreach($pieces as $original){
			$fixed = $original;

			preg_match('/^<([\S]+)(.*?)?>$/', $original, $m);

			$tag = strtolower($m[1]);
			$attrString = trim($m[2]);
			//remove self close tag /
			$attrString = trim($attrString, '/');

			// remove blank in close tags
			// http://www.w3.org/TR/2014/CR-html5-20140731/syntax.html#end-tags
			// 8.1.2.2 End tags
			if( substr($tag, 0, 1) == '/' ){
				$fixed = '<' . $tag . '>';
			}else{
				$attrs = array();
				while( $attrString ){
					$attrString = trim($attrString) . ' ';
					$foundAttr = FALSE;
					foreach(array(
						'EMPTY' => '/^([^=|\s|\"|\']+)\s/',  //Empty attribute syntax
						'UNQUOTED' => '/^([^=|\s]+)=([^=|\s|\"|\']+)\s/', //Unquoted attribute value syntax
						'S-QUOTED' => '/^([^=|\s]+)=\'(.*?)\'\s/', //Single-quoted attribute value syntax
						'D-QUOTED' => '/^([^=|\s]+)=\"(.*?)\"\s/', //Double-quoted attribute value syntax
					) as $style => $preg ){
						if( preg_match($preg, $attrString, $m) ){
							$key = 	strtolower($m[1]);
							$attrs[$key] = array(
								'style' => $style,
								'value' => isset($m[2]) ? $m[2] : NULL,
							);
							$attrString = substr($attrString, strlen($m[0]));
							$foundAttr = TRUE;
						}
					}
					if(!trim($attrString) || !$foundAttr){
						break;
					}
				}



				$attrArray = array();
				foreach($attrs as $key => $attr){
					$style = $attr['style'];
					$value = $attr['value'];

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

					if($style == 'S-QUOTED' && strpos($value, '"') === FALSE){
						$style = 'D-QUOTED';
					}
					if($style == 'EMPTY'){
						$attrArray[] = $key;
					}elseif($style == 'UNQUOTED' || $style == 'D-QUOTED'){
						//Unquoted attribute add double-quote mark 
						$attrArray[] = $key . '="' . $value . '"';
					}elseif($style == 'S-QUOTED'){
						$attrArray[] = $key . '=\'' . $value . '\'';
					}
				}

				$fixed = '<' . $tag . (empty($attrArray) ? '' : ' ' . implode(' ', $attrArray)) . '>';
			}

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
	foreach( $cache_special_tags_content as $tag => $content ){
		foreach( $content as $index => $string ){
			$string = trim($string, "\r\n\t");
			$html = str_replace( '[%~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~' . $tag .'~' . $index . '%]', $string, $html );
		}
	}

	return $html;
}