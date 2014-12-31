<?php
/*
Plugin Name: GD Booster
Plugin URI: http://wpgeodirectory.com/
Description: GD Booster wraps some of the smartest caching, compression and minifying methods available today for WordPress, modded to be 100% GeoDirectory compatible.
Version: 1.0.2
Author: GeoDirectory
Author URI: http://wpgeodirectory.com/
License: GPLv3
 
GD Booster is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
GD Booster is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with GD Booster. If not, see license.txt.
*/

if(!defined('WPINC')) // MUST have WordPress.
	exit('Do NOT access this file directly: '.basename(__FILE__));
	
define( 'GEODIR_GD_BOOSTER_VERSION', '1.0.2' );
if ( !defined( 'GEODIR_GD_BOOSTER_TEXTDOMAIN' ) ) {
	define( 'GEODIR_GD_BOOSTER_TEXTDOMAIN', 'geodir-gd-booster' );
}
if ( !defined( 'GD_BOOSTER_CACHE_DIR' ) ) {
	define( 'GD_BOOSTER_CACHE_DIR', str_replace('\\','/',dirname(__FILE__)).'/../../booster_cache' );
}

if(require(dirname(__FILE__).'/includes/wp-php53.php')) { // TRUE if running PHP v5.3+.
	require_once dirname(__FILE__).'/geodir-gd-booster.inc.php';
	
	if ( defined( 'GEODIR_GD_BOOSTER_ENABLE' ) && GEODIR_GD_BOOSTER_ENABLE ) {
		/* gd-booster */
		require_once dirname(__FILE__).'/booster_inc.php';
		
		add_action('wp_footer','gd_booster_wp', 100001);
	}
}
else wp_php53_notice('GD Booster');

if ( is_admin() ){
require_once('gd_update.php');	
}

function gd_booster_htaccess() {
	$wp_htacessfile = get_home_path().'.htaccess';
	$booster_htacessfile = rtrim(str_replace('\\','/',realpath(dirname(__FILE__))),'/').'/htaccess/.htaccess';
	if(file_exists($booster_htacessfile))
	{
		if(file_exists($wp_htacessfile) && is_writable($wp_htacessfile))
		{
			$wp_htacessfile_contents = file_get_contents($wp_htacessfile);
			$wp_htacessfile_contents = preg_replace('/#GEODIR-GD-Booster Start#################################################.*#GEODIR-GD-Booster End#################################################/ims','',$wp_htacessfile_contents);
			$wp_htacessfile_contents = $wp_htacessfile_contents.file_get_contents($booster_htacessfile);
		}
		else $wp_htacessfile_contents = file_get_contents($booster_htacessfile);
		@file_put_contents($wp_htacessfile,$wp_htacessfile_contents);
	}
	@mkdir(GD_BOOSTER_CACHE_DIR,0777);
	@chmod(GD_BOOSTER_CACHE_DIR,0777);
}

function gd_booster_cleanup() {
	// Remove entries from .htaccess
	$wp_htacessfile = get_home_path().'.htaccess';
	if(file_exists($wp_htacessfile) && is_writable($wp_htacessfile))
	{
		$wp_htacessfile_contents = file_get_contents($wp_htacessfile);
		$wp_htacessfile_contents = preg_replace('/#GEODIR-GD-Booster Start#################################################.*#GEODIR-GD-Booster End#################################################/ims','',$wp_htacessfile_contents);
		@file_put_contents($wp_htacessfile,$wp_htacessfile_contents);
	}
	
	// Remove all cache files
	$handle=opendir(GD_BOOSTER_CACHE_DIR);
	while(false !== ($file = readdir($handle)))
	{
		if($file[0] != '.' && is_file(GD_BOOSTER_CACHE_DIR.'/'.$file)) unlink(GD_BOOSTER_CACHE_DIR.'/'.$file);
	}
	closedir($handle);
}

function gd_booster_wp() {
	// Dump output buffer
	if($out = ob_get_contents())
	{
		// Check for right PHP version
		if(strnatcmp(phpversion(),'5.0.0') >= 0)
		{ 
			$booster_cache_dir = GD_BOOSTER_CACHE_DIR;
			$js_plain = '';
			$booster_out = '';
			$booster_folder = explode('/',rtrim(str_replace('\\','/',realpath(dirname(__FILE__))),'/'));
			$booster_folder = $booster_folder[count($booster_folder) - 1];
			$booster = new GDBooster();
			if(!is_dir($booster_cache_dir)) 
			{
				@mkdir($booster_cache_dir,0777);
				@chmod($booster_cache_dir,0777);
			}
			if(is_dir($booster_cache_dir) && is_writable($booster_cache_dir) && substr(decoct(fileperms($booster_cache_dir)),1) == "0777")
			{
				$booster_cache_reldir = $booster->getpath(str_replace('\\','/',realpath($booster_cache_dir)),str_replace('\\','/',dirname(__FILE__)));
			}
			else 
			{
				$booster_cache_dir = rtrim(str_replace('\\','/',dirname(__FILE__)),'/').'/../../booster_cache';
				$booster_cache_reldir = '../../booster_cache';
			}
			$booster->booster_cachedir = $booster_cache_reldir;
			$booster->js_minify = TRUE;
			$booster->js_closure_compiler = FALSE;
			
			// exclude js/css
			$exclude_js_css = $booster->geodir_exclude_js_css();
			$exclude_js = !empty($exclude_js_css) && isset($exclude_js_css['js']) ? $exclude_js_css['js'] : array();
			$exclude_css = !empty($exclude_js_css) && isset($exclude_js_css['css']) ? $exclude_js_css['css'] : array();
	
			// Calculate relative path from root to Booster directory
			$root_to_booster_path = $booster->getpath(str_replace('\\','/',dirname(__FILE__)),str_replace('\\','/',dirname(realpath(ABSPATH))));
			
			if(preg_match_all('/<head.*<\/head>/ims',$out,$headtreffer,PREG_PATTERN_ORDER) > 0)
			{
				$pagetreffer = $out;
				// Prevent processing of (conditional) comments
				$pagetreffer = preg_replace('/<!--.+?-->/ims','',$pagetreffer);
				
				// Detect charset
				if(preg_match('/<meta http-equiv="Content-Type" content="text\/html; charset=(.+?)" \/>/',$pagetreffer,$charset))
				{
					$pagetreffer = str_replace($charset[1],'',$pagetreffer);
					$charset = $charset[1];
				}
				else $charset = '';
				
				// CSS part
				$css_rel_files = array();
				$css_abs_files = array();
				// Start width inline-files
				preg_match_all('/<style[^>]*>(.*?)<\/style>/ims',$pagetreffer,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i<count($treffer[0]);$i++) 
				{
					// Get media-type
					if(preg_match('/media=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$mediatreffer)) 
					{
						$media = preg_replace('/[^a-z]+/i','',$mediatreffer[1]);
						if(trim($media) == '') $media = 'all';
					}
					else $media = 'all';
					$rel = 'stylesheet';
					
					// Create sub-arrays if not yet there
					if(!isset($css_rel_files[$media])) $css_rel_files[$media] = array();
					if(!isset($css_abs_files[$media])) $css_abs_files[$media] = array();
					if(!isset($css_rel_files[$media][$rel])) $css_rel_files[$media][$rel] = array();
					if(!isset($css_abs_files[$media][$rel])) $css_abs_files[$media][$rel] = array();

					// Save plain CSS to file to keep everything in line
					$css_plain_filename = md5($treffer[1][$i]).'_plain.css';
					
					$filename = $booster_cache_dir.'/'.$css_plain_filename;
					if ( !file_exists( $filename ) ) {
						@file_put_contents( $filename, $treffer[1][$i] );
					}
					
					@chmod($filename,0777);
		
					// Enqueue file to array
					$booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
					
					// Calculate relative path from Booster to file
					$booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
					$filename = $booster_to_file_path.'/'.$css_plain_filename;
					
					array_push($css_rel_files[$media][$rel],$filename);
					array_push($css_abs_files[$media][$rel],rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);

					$debug_text = '';
					if ( GEODIR_GD_BOOSTER_DEBUGGING_ENABLE ) {
						$debug_text = '<!-- Moved to file by GD Booster '.$css_plain_filename.' -->';
					}
					$pagetreffer = str_replace( $treffer[0][$i], $debug_text, $pagetreffer );
					$out = str_replace( $treffer[0][$i], $debug_text, $out );					
				}

				// Continue with external files
				preg_match_all('/<link[^>]*?href=[\'"]*?([^\'"]+?\.css)[\'"]*?[^>]*?>/ims',$pagetreffer,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i < count($treffer[0]);$i++) 
				{
					// Get media-type
					if(preg_match('/media=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$mediatreffer)) 
					{
						$media = preg_replace('/[^a-z]+/i','',$mediatreffer[1]);
						if(trim($media) == '') $media = 'all';
					}
					else $media = 'all';
	
					// Get relation
					if(preg_match('/rel=[\'"]*([^\'"]+)[\'"]*/ims',$treffer[0][$i],$reltreffer)) $rel = $reltreffer[1];
					else $rel = 'stylesheet';
	
					// Convert file's URI into an absolute local path
					if (strpos($treffer[1][$i],'https:') !== false) {
						$filename = preg_replace('/^https:\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
					}else{
						$filename = preg_replace('/^http:\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
					}
					//$filename = preg_replace('/^http:\/\/[^\/]+/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$treffer[1][$i]);
					// Remove any parameters from file's URI
					$filename = preg_replace('/\?.*$/','',$filename);
					// If file exists
					//$booster_out .= "###".$filename;
					if(file_exists($filename))
					{
						// If its a normal CSS-file
						if(substr($filename,strlen($filename) - 4,4) == '.css' && file_exists($filename))
						{
							// exclude css files
							if (basename($filename) != '' && !empty($exclude_css) && in_array(basename($filename), $exclude_css)) {
								$css_exclude_files[] = $treffer[0][$i];
									
								if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
									$out = str_replace($treffer[0][$i], '<!-- Excluded by GD Booster '.$treffer[0][$i].' -->', $out);
								} else {
									$out = str_replace(array($treffer[0][$i]."\r\n", $treffer[0][$i]."\r", $treffer[0][$i]."\n", $treffer[0][$i]),'',$out);
								}
							} else {
								// Put file-reference inside a comment
								if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
									$out = str_replace($treffer[0][$i],'<!-- Processed by GD Booster '.$treffer[0][$i].' -->',$out);
								} else {
									$out = str_replace(array($treffer[0][$i]."\r\n", $treffer[0][$i]."\r", $treffer[0][$i]."\n", $treffer[0][$i]),'',$out);
								}
			
								// Calculate relative path from Booster to file
								$booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
								$filename = $booster_to_file_path.'/'.basename($filename);
				
								// Create sub-arrays if not yet there
								if(!isset($css_rel_files[$media])) $css_rel_files[$media] = array();
								if(!isset($css_abs_files[$media])) $css_abs_files[$media] = array();
								if(!isset($css_rel_files[$media][$rel])) $css_rel_files[$media][$rel] = array();
								if(!isset($css_abs_files[$media][$rel])) $css_abs_files[$media][$rel] = array();
								
								// Enqueue file to respective array
								array_push($css_rel_files[$media][$rel],$filename);
								array_push($css_abs_files[$media][$rel],rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);
							}
						}
						else $out = str_replace($treffer[0][$i],$treffer[0][$i].'<!-- GD Booster skipped '.$filename.' -->',$out);
					}
					// Leave untouched but put calculated local file name into a comment for debugging
					else $out = str_replace($treffer[0][$i],$treffer[0][$i].'<!-- GD Booster had a problems finding '.$filename.' -->',$out);
				}
	
				// Creating Booster markup for each media and relation seperately
				$links = '';
				reset($css_rel_files);
				for($i=0;$i < count($css_rel_files);$i++) 
				{
					$media_rel = $css_rel_files[key($css_rel_files)];
					$media_abs = $css_abs_files[key($css_rel_files)];
					reset($media_rel);
					for($j=0;$j<count($media_rel);$j++) 
					{
						$booster->getfilestime($media_rel[key($media_rel)],'css');

						$media_rel[key($media_rel)] = implode(',',$media_rel[key($media_rel)]);
						$media_abs[key($media_rel)] = implode(',',$media_abs[key($media_rel)]);
						$link = '<link type="text/css" rel="'.key($media_rel).
						'" media="'.key($css_rel_files).
						'" href="'.get_option('siteurl').'/wp-content/plugins/'.
						$booster_folder.
						'/booster_css.php'.
						'?'.//($booster->mod_rewrite ? '/' : '?').
						'dir='.htmlentities(str_replace('..','%3E',$media_rel[key($media_rel)])).
						'&amp;cachedir='.htmlentities(str_replace('..','%3E',$booster_cache_reldir),ENT_QUOTES).
						($booster->debug ? '&amp;debug=1' : '').
						($booster->librarydebug ? '&amp;librarydebug=1' : '').
						'&amp;nocache='.$booster->filestime.'" />';
						
						if(key($css_rel_files) != 'print')
						{
							$links .= $link."\r\n";
						}
						else
						{
							$links .= '<noscript>'.$link.'</noscript>'."\r\n";
							$js_plain .= 'jQuery(document).ready(function () {
								jQuery("head").append("'.addslashes($link).'");
							});
							';
						}
						$links .= "\r\n";
						next($media_rel);
					}
					next($css_rel_files);
				}

				// Insert markup for normal browsers and IEs (CC's now replacing former UA-sniffing)
				if($charset != '') $booster_out .= '<meta http-equiv="Content-Type" content="text/html; charset='.$charset.'" />'."\r\n";
				$booster_out .= '<!--[if IE]><![endif]-->'."\r\n";
				$booster_out .= '<!--[if (gte IE 8)|!(IE)]><!-->'."\r\n";
				$booster_out .= $links;
				$booster_out .= '<!--<![endif]-->'."\r\n";
				$booster_out .= '<!--[if lte IE 7 ]>'."\r\n";
				$booster_out .= str_replace('booster_css.php','booster_css_ie.php',$links);
				$booster_out .= '<![endif]-->'."\r\n";
				if (!empty($css_exclude_files)) {
					$booster_out .= implode("\r\n", $css_exclude_files);
				}
				
				// Injecting the result
				$out = str_replace('</title>',"</title>\r\n".$booster_out,$out);
				$booster_out = '';				
				
				// JS-part
				$js_rel_files = array();
				$js_abs_files = array();
				$js_parameters = array();
				$js_exclude_files = array();
				$js_external_files = array();
				preg_match_all('/<script[^>]*>(.*?)<\/script>/ims',$pagetreffer,$treffer,PREG_PATTERN_ORDER);
				for($i=0;$i<count($treffer[0]);$i++) 
				{	
				
				
					

					
					if(preg_match('/<script.*?src=[\'"]*([^\'"]+\.js)\??([^\'"]*)[\'"]*.*?<\/script>/ims',$treffer[0][$i],$srctreffer))
					{	
						
						// Get Domainname
						$host = isset($_SERVER['SCRIPT_URI']) ? parse_url($_SERVER['SCRIPT_URI'],PHP_URL_HOST) : $_SERVER['HTTP_HOST'];
						// Convert siteurl into a regex-safe expression
						$host = str_replace(array('/','.'),array('\/','\.'),$host);
						// Convert file's URI into an absolute local path
						if (strpos($srctreffer[1],'https:') !== false) {
						$filename = preg_replace('/^https:\/\/'.$host.'[^\/]*/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$srctreffer[1]);
					}else{
						$filename = preg_replace('/^http:\/\/'.$host.'[^\/]*/',rtrim($_SERVER['DOCUMENT_ROOT'],'/'),$srctreffer[1]);
					}
						
						// If file is external
						if(substr($filename,0,7) == 'http://')
						{
							// Skip processing of external files altogether
							$js_external_files[] = $srctreffer[0];
							$debug_text = '';
							if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
								$debug_text = '<!-- Processed by GD Booster external file '.$srctreffer[0].' -->';
							}
							$out = str_replace( $srctreffer[0], $debug_text, $out );
						}
						// If file is internal and does exist
						elseif(file_exists($filename))
						{
							// If its a normal JavaScript-file
							if(substr($filename,strlen($filename) - 3,3) == '.js')
							{
								// exclude js files
								if (basename($filename) != '' && !empty($exclude_js) && in_array(basename($filename), $exclude_js)) {
									$js_exclude_files[] = $srctreffer[0];
									
									if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
										$out = str_replace($srctreffer[0], '<!-- Excluded by GD Booster '.$srctreffer[0].' -->', $out);
									} else {
										$out = str_replace(array($srctreffer[0]."\r\n", $srctreffer[0]."\r", $srctreffer[0]."\n", $srctreffer[0]),'',$out);
									}
								} else {
									// Remove any parameters from file's URI
									$filename = preg_replace('/\?.*$/','',$filename);
		
									// Put file-reference inside a comment
									if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
										$out = str_replace($srctreffer[0],'<!-- Processed by GD Booster '.$srctreffer[0].' -->',$out);
									} else {
										$out = str_replace(array($srctreffer[0]."\r\n", $srctreffer[0]."\r", $srctreffer[0]."\n", $srctreffer[0]),'',$out);
									}
				
									// Calculate relative path from Booster to file
									$booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
									$filename = $booster_to_file_path.'/'.basename($filename);
					
									// Enqueue file to array
									array_push($js_rel_files,$filename);
									array_push($js_abs_files,rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);
								}
							}
							else $out = str_replace($srctreffer[0],$srctreffer[0].'<!-- GD Booster skipped '.$filename.' -->',$out);
						}
						// Leave untouched but put calculated local file name into a comment for debugging
						else $out = str_replace($srctreffer[0],$srctreffer[0].'<!-- GD Booster had a problems finding '.$filename.' -->',$out);
					} else {
						if(preg_match('/<script.*? src=[\'"]*([^\'"])\??([^\'"]*)[\'"]*.*?<\/script>/ims',$treffer[0][$i],$srctreffer) && !preg_match('/<script.*?<iframe.*?<\/script>/ims',$treffer[0][$i],$srctreffer_na)) {
							// Skip processing of external files altogether
							//$booster_out .=  '###'.$srctreffer[0].'###';
							//$booster_out .=  '###'.$treffer[0][$i].'###';
							//$booster_out .=  '####'.print_r($srctreffer).'####';
							$js_external_files[] = $srctreffer[0];
							$debug_text = '';
							if (GEODIR_GD_BOOSTER_DEBUGGING_ENABLE) {
								$debug_text = '<!-- Processed by GD Booster external file '.$srctreffer[0].' -->';
							}
							$out = str_replace( $srctreffer[0], $debug_text, $out );
						} else {
							// Save plain JS to file to keep everything in line
							$js_plain_filename = md5($treffer[1][$i]).'_plain.js';
							
							$filename = $booster_cache_dir.'/'.$js_plain_filename;
							if ( !file_exists( $filename ) ) {
								@file_put_contents( $filename, $treffer[1][$i] );
							}
							
							@chmod($filename,0777);
				
							// Enqueue file to array
							$booster_to_file_path = $booster->getpath( str_replace( '\\','/', dirname( $filename ) ),str_replace( '\\', '/', dirname( __FILE__ ) ) );
							// Calculate relative path from Booster to file
							$booster_to_file_path = $booster->getpath(str_replace('\\','/',dirname($filename)),str_replace('\\','/',dirname(__FILE__)));
							$filename = $booster_to_file_path.'/'.$js_plain_filename;
					
							array_push( $js_rel_files, $filename );
							array_push( $js_abs_files, rtrim(str_replace('\\','/',dirname(realpath(ABSPATH))),'/').'/'.$root_to_booster_path.'/'.$filename);
		
							$debug_text = '';
							if ( GEODIR_GD_BOOSTER_DEBUGGING_ENABLE ) {
								$debug_text = '<!-- Moved to file by GD Booster '.$js_plain_filename.' -->';
							}
							$pagetreffer = str_replace( $treffer[0][$i], $debug_text, $pagetreffer );
							$out = str_replace( $treffer[0][$i], $debug_text, $out );
						}
					}
				}
				
				// Creating Booster markup
				$js_rel_files = implode(',',$js_rel_files);
				$js_abs_files = implode(',',$js_abs_files);
				$js_plain = preg_replace('/\/\*.*?\*\//ims','',$js_plain);
				$js_plain .= 'try {document.execCommand("BackgroundImageCache", false, true);} catch(err) {}';
				
				if (!empty($js_external_files)) {
					$booster_out .= "\r\n" . implode("\r\n", $js_external_files) . "\r\n";
				}
				
				$booster_out .= '<script type="text/javascript" src="'.
				get_option('siteurl').'/wp-content/plugins/'.$booster_folder.'/booster_js.php?dir='.
				htmlentities(str_replace('..','%3E',$js_rel_files)).
				'&amp;cachedir='.htmlentities(str_replace('..','%3E',$booster_cache_reldir),ENT_QUOTES).
				(($booster->debug) ? '&amp;debug=1' : '').
				((!$booster->js_minify) ? '&amp;js_minify=0' : '').
				(($booster->js_closure_compiler) ? '&amp;js_cc=1' : '').
				'&amp;nocache='.$booster->filestime.
				'?'.implode('&amp;',$js_parameters).'"></script>';
				if (!empty($js_exclude_files)) {
					$booster_out .= "\r\n" . implode("\r\n", $js_exclude_files) . "\r\n";
				}
				$booster_out .= '<script type="text/javascript">'.$js_plain.'</script>';
				$booster_out .= "\r\n";
				#$booster_out .= "\r\n<!-- ".$js_abs_files." -->\r\n";
				
				// Injecting the result at the bottom
				//$out = str_replace('</head>',$booster_out.'</head>',$out);
				///*
				if ( strpos( $out, "</body>" ) !== false ) {
					$out = str_replace('</body>',$booster_out.'</body>',$out);
				} else {
					$out .= $booster_out;
				}
				//*/
			}
		}
		else $out = str_replace('<body','<div style="display: block; padding: 1em; background-color: #FFF9D0; color: #912C2C; border: 1px solid #912C2C; font-family: Calibri, \'Lucida Grande\', Arial, Verdana, sans-serif; white-space: pre;">You need to upgrade to PHP 5 or higher to have CSS-JS-Booster work. You currently are running on PHP '.phpversion().'</div><body',$out);
		
		// Recreate output buffer
		ob_end_clean();
		if (
		isset($_SERVER['HTTP_ACCEPT_ENCODING']) 
		&& substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') 
		&& function_exists('ob_gzhandler') 
		&& (!ini_get('zlib.output_compression') || intval(ini_get('zlib.output_compression')) <= 0) 
		&& !function_exists('wp_cache_ob_callback')
		) @ob_start('ob_gzhandler');
		elseif(function_exists('wp_cache_ob_callback')) @ob_start('wp_cache_ob_callback');
		else @ob_start();
		
		// Output page
		echo $out;
	}
}

// wordpress SEO fix
add_filter( 'wpseo_json_ld_search_output', 'gd_booster_wordpress_seo_fix', 10, 1 ); 

function gd_booster_wordpress_seo_fix($code){
	if (strpos($code,'[') !== false) {
    	//they fixed it
	}else{
	//we fix it
	$code = str_replace('<script type="application/ld+json">', '<script type="application/ld+json">[', $code);
	$code = str_replace('</script>', ']</script>', $code);
	}
	return 	$code;
}