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

class MediaWikiJavaScript {
	
	protected $mIO, $mPipe;
	
	function tag($input, $args, &$parser) {
		// ToDo: Convert args to json
		
		return $this->wrap($input);
	}
	
	function jseval(&$parser, $param = false) {
		if(!$param)
			return '';
		return $parser->recursiveTagParse($this->wrap($param, true));
	}
	
	function wrap($jscode, $eval = false) {
		global $wgJSMaxRead;
		if(!$this->mIO)
			$this->open();
		
		$jsonMessage = self::jsonStringify(array('action' => 'exec', 'code' => $jscode));
		if ( strpos($jsonMessage, "\0\0") )
			return "Illegal delimiter sequence detected, potentially malicious user input, aborting";
		fwrite( $this->mPipe[0], "$jsonMessage\0\0" );
		$json = stream_get_line( $this->mPipe[1], $wgJSMaxRead, "\0\0");
		$msg = self::jsonParse( $json );
		if ( $msg ) {
			return $msg['output'];
		}
	}
	
	function clearState( &$parser ) {
		$this->reset();
	}
	
	function open() {
		// ToDo: Longrunning sockets
		global $wgJavaHome, $wgRhinoExternalJar;
		$JAVA = "java";
		if( $wgJavaHome )
			$JAVA = "$wgJavaHome/$JAVA";
		
		$this->mPipe = array();
		// we have to use Xbootclasspath due to a bug where Rhino bundled with OpenJDK clobers the jar we specify which may be newer
		$this->mIO = proc_open("$JAVA -Xbootclasspath/p:\"$wgRhinoExternalJar\" -jar \"$wgMWJSExternalJar\" -", array(
			array( 'pipe', 'w' ),
			array( 'pipe', 'r' ),
			array( 'pipe', 'r' ) // ToDo: Should we redirect stderr to file right here?
		), $this->mPipe);
		
	}
	
	function reset() {
		
	}
	
	function close() {
		foreach ( $this->mPipe as $pipe )
			fclose($pipe);
		unset($this->mPipe);
		proc_close($this->mIO);
		unset($this->mIO);
	}
	
	public static function jsonParse($jsonString) {
		if(!function_exists('json_decode')) {
			$s = new Services_JSON( SERVICES_JSON_LOOSE_TYPE | SERVICES_JSON_SUPPRESS_ERRORS );
			return $s->decode( $jsonString );
		} else {
			return json_decode( $jsonString, true );
		}
	}
	
	public static function jsonStringify($jsonData) {
		if(!function_exists('json_encode') || strtolower(json_encode("\xf0\xa0\x80\x80")) != '\ud840\udc00') {
			$s = new Services_JSON( SERVICES_JSON_LOOSE_TYPE | SERVICES_JSON_SUPPRESS_ERRORS );
			return $s->encode( $jsonData );
		} else {
			return json_encode( $jsonData );
		}
	}
	
}

