<?php
/**
 * @author Daniel Friesen (http://mediawiki.org/wiki/User:Dantman)
 * @copyright Copyright © 2009 - Daniel Friesen
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if ( !defined( 'MEDIAWIKI' ) ) die( 'This file is a MediaWiki extension, it is not a valid entry point' );

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'JS Parser extensions',
	'version' => '0.1',
	'author' => '[http://www.mediawiki.org/wiki/User:Dantman Daniel Friesen]',
	'url' => 'http://www.mediawiki.org/wiki/Extension:JS',
	'description' => 'Extends the parser with support for embedded blocks of JavaScript',
	'descriptionmsg' => 'mwjs_desc',
);

$dir = dirname(__FILE__);
$wgExtensionFunctions[] = 'wfSetupMediaWikiJavaScript';
$wgAutoloadClasses['MediaWikiJavaScript'] = "$dir/MediaWikiJavaScript.php";
$wgHooks['LanguageGetMagic'][] = 'MediaWikiJavaScriptStub::magic';

function wfSetupMediaWikiJavaScript() {
	global $wgParser, $wgMWJSStub, $wgHooks;
	
	$wgMWJSStub = new MediaWikiJavaScriptStub;
	
	// Check for SFH_OBJECT_ARGS capability
	if ( defined( 'MW_SUPPORTS_PARSERFIRSTCALLINIT' ) ) {
		$wgHooks['ParserFirstCallInit'][] = array( &$wgMWJSStub, 'registerParser' );
	} else {
		if ( class_exists( 'StubObject' ) && !StubObject::isRealObject( $wgParser ) ) {
			$wgParser->_unstub();
		}
		$wgMWJSStub->registerParser( $wgParser );
	}
	
	$wgHooks['ParserClearState'][] = array( &$wgMWJSStub, 'clearState' );
}

class MediaWikiJavaScriptStub {
	
	private $mJS;
	function registerParser( &$parser ) {
		$parser->setFunctionHook( 'jseval', array( &$this, 'jseval' ) );
		$parser->setHook( 'js', array( &$this, 'tag' ) );
		return true;
	}
	
	function magic( &$magicWords, $langCode ) {
		$magicWords['jseval'] = array(0, 'jseval');
		return true;
	}
	
	/** Defer ParserClearState */
	function clearState( &$parser ) {
		if ( !is_null( $this->realObj ) ) {
			$this->realObj->clearState( $parser );
		}
		return true;
	}
	
	/** Pass through function call */
	function __call( $name, $args ) {
		if ( is_null( $this->mJS ) ) {
			$this->mJS = new MediaWikiJavaScript;
			$this->mJS->clearState( $args[0] );
		}
		return call_user_func_array( array( $this->mJS, $name ), $args );
	}
}

$wgJavaHome = null;
$wgRhinoExternalJar = "$dir/js.jar";
$wgMWJSExternalJar = "$dir/mwjs.jar";
$wgJSMaxLines = 1000000;
$wgJSMaxRead = 1000000;
$wgJSMaxTime = 3;


