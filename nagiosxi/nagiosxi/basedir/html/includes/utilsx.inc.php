<?php
//
// EXTERNALLY AUTHORED FUNCTIONS
// All function code copyright by respective authors.  Nagios Enterprises makes no claims of ownership over these functions.
//
// $Id: utilsx.inc.php 1061 2012-03-01 22:50:12Z egalstad $

include_once("utilsx-json.php");


// checks if a URL is valid
// http://www.phpcentral.com/208-url-validation-php.html
function valid_url($url){

	if(!have_value($url))
		return false;
		
	// REMOVED 01/10/2011 EG - didn't handle ampersands...
	//return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
	
	// SCHEME
	$urlregex = "^(https?|ftp)\:\/\/";

	// USER AND PASS (optional)
	$urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

	// HOSTNAME OR IP
	$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*"; // http://x = allowed (ex. http://localhost, http://routerlogin)
	//$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+"; // http://x.x = minimum
	//$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}"; // http://x.xx(x) = minimum
	//use only one of the above

	// PORT (optional)
	$urlregex .= "(\:[0-9]{2,5})?";
	// PATH (optional)
	$urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
	// GET Query (optional)
	$urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
	// ANCHOR (optional)
	$urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?\$";

	// check
	if (eregi($urlregex, $url)){
		return true;
		}
	else{
		return false;
		}
	}


/**
 * Like htmlentities but for XML
 * @param   string   $string    The stirng you want to escape
 * @return  XML-escaped string
 *
 *  XML Entity Mandatory Escape Characters
 *  code copied from http://www.php.net/htmlentities
 */

function xmlentities($string){

	//bug fix for "segmentation fault error".  Output that is too long with crash the preg_match_all function. 
	$length = strlen($string); 
	if($length > 2000)
	{
			//split huge string into 2 to prevent memory crashing errors in apache 
			$string1 = substr($string,0,2000);
			$max = ($length > 4000) ? 4000 : $length; //set max length to 4k. 
			$string2 = substr($string,2000,$max);  //ndoutils cuts off data after 8k 
			$data1=str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string1 );
			$data2=str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string2 );
			
			$clean_output = ''; 
			preg_match_all('/([\x09\x0a\x0d\x20-\x7e]'. // ASCII characters
				'|[\xc2-\xdf][\x80-\xbf]'. // 2-byte (except overly longs)
				'|\xe0[\xa0-\xbf][\x80-\xbf]'. // 3 byte (except overly longs)
				'|[\xe1-\xec\xee\xef][\x80-\xbf]{2}'. // 3 byte (except overly longs)
				'|\xed[\x80-\x9f][\x80-\xbf])+/', // 3 byte (except UTF-16 surrogates)
				$data1, $clean_pieces );
			$clean_output = join('?', $clean_pieces[0] );	
			preg_match_all('/([\x09\x0a\x0d\x20-\x7e]'. // ASCII characters
				'|[\xc2-\xdf][\x80-\xbf]'. // 2-byte (except overly longs)
				'|\xe0[\xa0-\xbf][\x80-\xbf]'. // 3 byte (except overly longs)
				'|[\xe1-\xec\xee\xef][\x80-\xbf]{2}'. // 3 byte (except overly longs)
				'|\xed[\x80-\x9f][\x80-\xbf])+/', // 3 byte (except UTF-16 surrogates)
				$data2, $clean_pieces );	
			$clean_output .= join('?', $clean_pieces[0] );	 
	}
	else 
	{
		$data=str_replace ( array ( '&', '"', "'", '<', '>' ), array ( '&amp;' , '&quot;', '&apos;' , '&lt;' , '&gt;' ), $string );

		preg_match_all('/([\x09\x0a\x0d\x20-\x7e]'. // ASCII characters
		'|[\xc2-\xdf][\x80-\xbf]'. // 2-byte (except overly longs)
		'|\xe0[\xa0-\xbf][\x80-\xbf]'. // 3 byte (except overly longs)
		'|[\xe1-\xec\xee\xef][\x80-\xbf]{2}'. // 3 byte (except overly longs)
		'|\xed[\x80-\x9f][\x80-\xbf])+/', // 3 byte (except UTF-16 surrogates)
		$data, $clean_pieces );

		$clean_output = join('?', $clean_pieces[0] );
	}
	return $clean_output;

}


	
/**
 * See http://www.bin-co.com/php/scripts/load/
 * Version : 1.00.A
 * License: BSD
 */
/* renamed to load_url */
function load_url($url,$options=array('method'=>'get','return_info'=>false),$use_proxy=false) {

	// added 04-28-08 EG added a default timeout of 15 seconds
	if(!isset($options['timeout']))
		$options['timeout']=15;

    $url_parts = parse_url($url);

    $info = array(//Currently only supported by curl.
        'http_code'    => 200
    );
    $response = '';
    
    $send_header = array(
        'Accept' => 'text/*',
        'User-Agent' => 'BinGet/1.00.A (http://www.bin-co.com/php/scripts/load/)'
    );

    ///////////////////////////// Curl /////////////////////////////////////
    //If curl is available, use curl to get the data.
    if(function_exists("curl_init") 
                and (!(isset($options['use']) and $options['use'] == 'fsocketopen'))) { //Don't user curl if it is specifically stated to user fsocketopen in the options
        if(isset($options['method']) and $options['method'] == 'post') {
            $page = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
        } else {
            $page = $url;
        }

        $ch = curl_init($url_parts['host']);

	// added 04-28-08 EG set a timeout
	if(isset($options['timeout']))
		curl_setopt($ch, CURLOPT_TIMEOUT, $options['timeout']);
			
        curl_setopt($ch, CURLOPT_URL, $page);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Just return the data - not print the whole thing.
        curl_setopt($ch, CURLOPT_HEADER, true); //We need the headers
        curl_setopt($ch, CURLOPT_NOBODY, false); //The content - if true, will not download the contents
        if(isset($options['method']) and $options['method'] == 'post' and $url_parts['query']) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
        }
        //Set the headers our spiders sends
        curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); //The Name of the UserAgent we will be using ;)
        $custom_headers = array("Accept: " . $send_header['Accept'] );
        if(isset($options['modified_since']))
            array_push($custom_headers,"If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since'])));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);

        curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt"); //If ever needed...
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,1);  //FIX to add full SSL support -MG 7/15/2011
		
		//proxy options - added 10/12/2011 -MG
		if($use_proxy)
		{
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
			curl_setopt($ch, CURLOPT_PROXY, get_option('proxy_address'));
			curl_setopt($ch, CURLOPT_PROXYPORT, get_option('proxy_port'));
			curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
			//use auth credentials if specified 
			if(have_value(get_option('proxy_auth'))) 
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, get_option('proxy_auth'));
			
		}

        if(isset($url_parts['user']) and isset($url_parts['pass'])) {
            $custom_headers = array("Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
        }

        $response = curl_exec($ch);
        $info = curl_getinfo($ch); //Some information on the fetch
        curl_close($ch);

    //////////////////////////////////////////// FSockOpen //////////////////////////////
    } else { //If there is no curl, use fsocketopen
        if(isset($url_parts['query'])) {
            if(isset($options['method']) and $options['method'] == 'post')
                $page = $url_parts['path'];
            else
                $page = $url_parts['path'] . '?' . $url_parts['query'];
        } else {
            $page = $url_parts['path'];
        }

        $fp = fsockopen($url_parts['host'], 80, $errno, $errstr, 30);
        if ($fp) {
	
		// added 04-28-08 EG set a timeout
		if(isset($options['timeout']))
			stream_set_timeout($fp,$options['timeout']);
			
            $out = '';
            if(isset($options['method']) and $options['method'] == 'post' and isset($url_parts['query'])) {
                $out .= "POST $page HTTP/1.1\r\n";
            } else {
                $out .= "GET $page HTTP/1.0\r\n"; //HTTP/1.0 is much easier to handle than HTTP/1.1
            }
            $out .= "Host: $url_parts[host]\r\n";
            $out .= "Accept: $send_header[Accept]\r\n";
            $out .= "User-Agent: {$send_header['User-Agent']}\r\n";
            if(isset($options['modified_since']))
                $out .= "If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since'])) ."\r\n";

            $out .= "Connection: Close\r\n";
            
            //HTTP Basic Authorization support
            if(isset($url_parts['user']) and isset($url_parts['pass'])) {
                $out .= "Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']) . "\r\n";
            }

            //If the request is post - pass the data in a special way.
            if(isset($options['method']) and $options['method'] == 'post' and $url_parts['query']) {
                $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $out .= 'Content-Length: ' . strlen($url_parts['query']) . "\r\n";
                $out .= "\r\n" . $url_parts['query'];
            }
            $out .= "\r\n";

            fwrite($fp, $out);
            while (!feof($fp)) {
                $response .= fgets($fp, 128);
            }
            fclose($fp);
        }
    }

    //Get the headers in an associative array
    $headers = array();
	
	
	//added logging for connection failures
	if($info['http_code'] > 399 || $info['http_code'] < 200)
	{
		$f = @fopen('/usr/local/nagiosxi/var/load_url.log','w');
		@fwrite($f,"CURL ERROR\n TIME: ".date('c',time())."\n".print_r($info,true)."\nURL:\n $url \n OPTIONS: \n".print_r($options,true)."\nEND LOGENTRY\n\n");
		@fclose($f);
	}


    if($info['http_code'] == 404) {
        $body = "";
        $headers['Status'] = 404;
    } else {
        //Seperate header and content
	//echo "RESPONSE: ".$response."<BR><BR>\n";
	//exit();
        $separator_position = strpos($response,"\r\n\r\n");
        $header_text = substr($response,0,$separator_position);
        $body = substr($response,$separator_position+4);
	
	// added 04-28-2008 EG if we get a 301 (moved), another set of headers is received,
	if(substr($body,0,5)=="HTTP/"){
		$separator_position = strpos($body,"\r\n\r\n");
		$header_text = substr($body,0,$separator_position);
		$body = substr($body,$separator_position+4);
		}
	
        //echo "SEP: ".$separator_position."<BR><BR>\n";
	//echo "HEADER: ".$header_text."<BR><BR>\n";
	//echo "BODY: ".$body."<BR><BR>\n";
        
        foreach(explode("\n",$header_text) as $line) {
            $parts = explode(": ",$line);
            if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
        }
    }

    if($options['return_info'])
	return array('headers' => $headers, 'body' => $body, 'info' => $info);
    return $body;
}


// implements json_encode for PHP < 5.2
// http://au.php.net/manual/en/function.json-encode.php#82904
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


function parse_argv($argv){
    array_shift($argv);
    $out=array();
    foreach($argv as $arg){
	
        if(substr($arg,0,2)=='--'){
			$eq=strpos($arg,'=');
            if($eq===false){
                $key=substr($arg,2);
                $out[$key]=isset($out[$key])?$out[$key]:true;
				} 
			else{
                $key=substr($arg,2,$eq-2);
                $out[$key]=substr($arg,$eq+1);
				}
			} 
			
		else if(substr($arg,0,1)=='-'){
            if(substr($arg,2,1)=='='){
                $key=substr($arg,1,1);
                $out[$key]=substr($arg,3);
				}
			else{
                $chars=str_split(substr($arg,1));
                foreach($chars as $char){
                    $key=$char;
                    $out[$key]=isset($out[$key])?$out[$key]:true;
					}
				}
			} 
		else{
            $out[] = $arg;
			}
		}
		
    return $out;
	}
	
// http://www.php.net/manual/en/function.scandir.php#90628
function file_list($d,$x){
	$l=array();
     foreach(array_diff(scandir($d),array('.','..')) as $f){
		//echo "EXAMINING: $f\n";
		if(is_file($d.'/'.$f) && (($x)?preg_match($x,$f):1))
			$l[]=$f;
		}
     return $l;
	} 	
	
// from http://www.php.net/manual/en/function.fileperms.php
function file_perms_to_string($perms){

	$info="";
	
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
				(($perms & 0x0800) ? 's' : 'x' ) :
				(($perms & 0x0800) ? 'S' : '-'));

	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
				(($perms & 0x0400) ? 's' : 'x' ) :
				(($perms & 0x0400) ? 'S' : '-'));

	// World
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
				(($perms & 0x0200) ? 't' : 'x' ) :
				(($perms & 0x0200) ? 'T' : '-'));
			
	return $info;
	}
	
/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 */
 // http://www.php.net/manual/en/function.unlink.php#87045
function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
        }

        if (!@unlink($dir . '/' . $obj))
        {
            unlinkRecursive($dir.'/'.$obj, true);
        }
    }

    closedir($dh);
   
    if ($deleteRootToo)
    {
        @rmdir($dir);
    }
   
    return;
} 


// NOTE - This does not work as expected
/*
//http://www.php.net/manual/en/function.json-decode.php#91216
if ( !function_exists('json_decode') ){
function json_decode($json)
{ 
    // Author: walidator.info 2009
    $comment = false;
    $out = '$x=';
   
    for ($i=0; $i<strlen($json); $i++)
    {
        if (!$comment)
        {
            if ($json[$i] == '{')        $out .= ' array(';
            else if ($json[$i] == '}')    $out .= ')';
            else if ($json[$i] == ':')    $out .= '=>';
            else                         $out .= $json[$i];           
        }
        else $out .= $json[$i];
        if ($json[$i] == '"')    $comment = !$comment;
    }
    eval($out . ';');
    return $x;
} 
} 
*/

/*

// Cred: http://abeautifulsite.net/notebook/71
if(!function_exists('json_encode')) {
    function json_encode($data) {
        $json = new Services_JSON();
        return( $json->encode($data) );
    }
}
*/
// Cred: http://abeautifulsite.net/notebook/71
if( !function_exists('json_decode') ) {
    function json_decode($data, $bool=null) {
        if ($bool) {
            $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        } else {
            $json = new Services_JSON();
        }
        return( $json->decode($data) );
    }
	}
	

// multi-dimensional array sort
// improved version of that found at: http://www.firsttube.com/read/sorting-a-multi-dimensional-array-with-php/
function array_sort_by_subval($a,$subkey,$reverse=false) {
	$b=array();
	foreach($a as $k=>$v) {
		$b[$k] = strtolower($v[$subkey]);
	}
	if($reverse==false)
		asort($b);
	else
		arsort($b);
	$c=array();
	foreach($b as $key=>$val) {
		$c[] = $a[$key];
	}
	return $c;
}

?>