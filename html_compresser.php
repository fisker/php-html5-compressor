<?php
//is html 5  
//<!DOCTYPE html>
//fragments



function singleElementParser($string){
	if( !preg_match('/^<(.*?)>$/s', $string) ){
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
				'language' => 'javascript',
				//no src charset is useless
			),
			'style' => array(
				'type' => 'text/css',
			),
			'link' => array(
				'type' => 'text/css',
			),
			'form' => array(
				'method' => 'get',
			),
			'input' => array(
				'type' => 'text',
			),
			'area' => array(
				'shape' => 'rect',
			),

			//link rel="stylesheet" type="text/css"
		);

		if(isset($deafultAttributes[$tag]) 
			&& isset($deafultAttributes[$tag][$key])
			&& $deafultAttributes[$tag][$key] === $value){
			continue;
			//unset($attrs[$attrs]);
		}

		$booleanAttrs = array(
			'audio' => 'autoplay controls loop muted',
			'button' => 'autofocus disabled formnovalidate', //formnovalidate Only for type="submit"
			'details' => 'open',
			'dir' => 'compact',
			'fieldset' => 'disabled',
			'form' => 'novalidate',
			'frame' => 'noresize', //?
			'hr' => 'noshade',
			'iframe' => 'seamless',
			'img' => 'ismap',
			'input' => 'autofocus checked disabled formnovalidate multiple readonly required',
			'keygen' => 'autofocus challenge disabled',
			'menuitem' => 'checked default disabled',
			'object' => 'declare',
			'ol' => 'compact reversed',
			'optgroup' => 'disabled',
			'option' => 'disabled selected',
			'script' => 'async defer',
			'select' => 'autofocus disabled multiple required',
			'style' => 'scoped',
			'table' => 'sortable',
			'td' => 'nowrap',
			'textarea' => 'autofocus disabled readonly required',
			'th' => 'nowrap',
			'track' => 'default',
			'ul' => 'compact',
			'video' => 'autoplay controls loop muted', //same as audio	
		);

		//(/^(?:allowfullscreen|async|autofocus|autoplay|checked|compact|controls|declare|default|defaultchecked|defaultmuted|defaultselected|defer|disabled|enabled|formnovalidate|hidden|indeterminate|inert|ismap|itemscope|loop|multiple|muted|nohref|noresize|noshade|novalidate|nowrap|open|pauseonexit|readonly|required|reversed|scoped|seamless|selected|sortable|spellcheck|truespeed|typemustmatch|visible)$/i).test(attrName);

		//global boolean attribute
		if($key == 'hidden'){
			$value = NULL;
		}

		if(isset($booleanAttrs[$tag]) 
			&& in_array($key, explode(' ', $booleanAttrs[$tag]))){
			$value = NULL;
		}


		if( is_null($value) ){
			$attrArray[] = $key;
		}elseif( strpos($value, '"') !== FALSE ){
			$attrArray[] = $key . '=\'' . html_encode($value) . '\'';
		}else{
			$attrArray[] = $key . '="' . html_encode($value) . '"';
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

	// get all tags
	if(preg_match_all('/<[^!][^>]+>/s', $html, $matches)){
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

		$html = str_replace(array_keys($fixedPieces), array_values($fixedPieces),$html);

	}

	// replace all space characters to U+0020 SPACE,
	// "tab" (U+0009), "LF" (U+000A), "FF" (U+000C), and "CR" (U+000D).
	$html = preg_replace('/\t|\n|\f|\r/', ' ', $html);

	// remove whitespace
	$html = trim($html);
	$html = preg_replace('/\s+/', ' ', $html);

	// remove whitespace between tags
	//$html = str_replace('> <', '><', $html);
	// remove whitespace near html tags
	$html = str_replace('> ', '>', $html);
	$html = str_replace(' <', '<', $html);

	// retore special tag
	// TODO: remove new line after the special tag
	while(preg_match('/<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~/', $html)){
		foreach( $cache_special_tags_content as $tag => $content ){
			foreach( $content as $index => $string ){
				//inner content in code/pre should not be trimed
				if($tag === 'style'){
					$string = preg_replace_callback(
						'/^(<' . $tag . '(?:[^>]*?)>)(.*?)(<\/' . $tag . '>)$/is',
						function($matches){
							return singleElementParser($matches[1]) . 
								compress_css($matches[2]). 
								singleElementParser($matches[3]);
						}, 
						$string);
				}elseif( $tag === 'code' || $tag === 'pre' || $tag === 'textarea' ){
					// http://www.w3.org/TR/html5/single-page.html
					// 8.4 Parsing HTML fragments 
					// textarea is RCDATA,  code/pre re PLAINTEXT

					$string = preg_replace_callback(
						'/^(<' . $tag . '(?:[^>]*?)>)(.*?)(<\/' . $tag . '>)$/is',
						function($matches){
							return singleElementParser($matches[1]) . 
								compressPlainText($matches[2]). 
								singleElementParser($matches[3]);
						}, 
						$string);
				}else{
					$string = preg_replace_callback(
						'/^(<' . $tag . '(?:[^>]*?)>)(.*?)(<\/' . $tag . '>)$/is',
						function($matches){
							return singleElementParser($matches[1]) . 
								trim($matches[2]). 
								singleElementParser($matches[3]);
						}, 
						$string);
				}
				$html = str_replace( '<!~~HTML~COMPRESS~PLACEHOLDER~' . $guid .'~' . $tag .'~' . $index . '~~>', $string, $html );
			}
		}
	}

	return $html;
}


function html_encode($s){
	return str_replace(
		array("\t", "\n", "\f", "\r"),
		//array('&Tab;','&NewLine;','&#12;','&#13;'),
		array('&#9;','&#10;','&#12;','&#13;'),
		$s
	);

}



function compressPlainText($s){
	return str_replace("\n", array('&#10;'), $s);
	//return preg_replace('/\t/','&#9;', $s);

	// "tab" (U+0009), "LF" (U+000A), "FF" (U+000C), and "CR" (U+000D).
	//return str_replace(array("\t","\n","\f","\r"), array('&Tab;','&NewLine;','',''), $s);

	//return str_replace(array("\t","\n","\f","\r"), array('&#9;','&#10;','',''), $s);
}


/*
	Name: PHP CSS Compressor.
	Description: A simple PHP functions that compress css codes
	Version : 1.00
	Author: Linesh Jose
	Url: http://lineshjose.com
	Email: lineshjose@gmail.com
	Donate:  http://bit.ly/donate-linesh
	github: https://github.com/lineshjose
	Demo: http://lineshjose.com/blog/how-to-create-a-simple-css-compressor-using-php/
	Copyright: Copyright (c) 2012 LineshJose.com
	
	Note: This script is free; you can redistribute it and/or modify  it under the terms of the GNU General Public License as published by 	the Free Software Foundation; either version 2 of the License, or (at your option) any later version.This script is distributed in the hope 	that it will be useful,    but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the  GNU General Public License for more details.

----------------------------------------------------------------------------------------------------------------------

	This function returns compressed css codes
	@param $css_codes : CSS Code
*/

// Compress CSS function
function compress_css($css_codes)
{		
	$buffer =$css_codes;
	// Remove comments
	$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
	
	// Remove whitespace
	$buffer = str_replace(': ', ':', $buffer);
	$buffer = str_replace(' :', ':', $buffer);
	$buffer = str_replace(' ;', ';', $buffer);
	$buffer = str_replace('; ', ';', $buffer);
	$buffer = str_replace('{ ', '{', $buffer);
	$buffer = str_replace(' {', '{', $buffer);
	$buffer = str_replace('} ', '}', $buffer);
	$buffer = str_replace(' }', '}', $buffer);
	$buffer = str_replace(' ,', ',', $buffer);
	$buffer = str_replace(', ', ',', $buffer);
	$buffer = str_replace('  .', ' .', $buffer);
	$buffer = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);
	$buffer = str_replace(array('   ', '  '), ' ', $buffer);
	return	 $buffer;
}

//yui css compressor
//https://github.com/yui/yuicompressor/blob/master/src/com/yahoo/platform/yui/compressor/CssCompressor.java

//clean css
//https://github.com/jakubpawlowicz/clean-css