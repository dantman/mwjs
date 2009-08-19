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
		
		if ( strpos($jscode, "\0\0") )
			return "Illegal delimiter sequence detected, potentially malicious user input, aborting";
		fwrite( $this->mPipe[0], "exec\0$jscode\0\0" );
		while($msg = $this->readMessage()) {
			switch($msg[0]) {
			case 'output':
				return $msg[1];
			default:
				wfVarDump($msg);
				return "Unknown message returned";
			}
		}
		return "Timed out or could not read message";
	}
	
	private $mBuffer = "";
	private function readMessage() {
		global $wgJSMaxTime;
		for(;;) {
			$p = strpos($this->mBuffer, "\0\0");
			if( $p !== false )
				break;
			if( feof($this->mPipe[1]) )
				die("...");//return false;
			$read = array($this->mPipe[1]);
			$write = null;
			$except = null;
			$numChangedStreams = stream_select($read, $write, $except, $this->mEnd-time());
			if ( $numChangedStreams > 0 )
				$this->mBuffer .= fread($this->mPipe[1], 512);
			else
				return false;
			if ( time() >= $this->mEnd )
				return false;
		}
		$msgData = substr($this->mBuffer, 0, $p);
		$this->mBuffer = substr($this->mBuffer, $p+2);
		return explode("\0", $msgData);
	}
	
	function clearState( &$parser ) {
		$this->reset();
	}
	
	function open() {
		// ToDo: Socket server connections
		global $wgJavaHome, $wgRhinoExternalJar, $wgMWJSExternalJar;
		$JAVA = "java";
		if( $wgJavaHome )
			$JAVA = "$wgJavaHome/$JAVA";
		
		$this->mPipe = array();
		// we have to use Xbootclasspath due to a bug where Rhino bundled with OpenJDK clobers the jar we specify which may be newer
		//die("$JAVA -Xbootclasspath/p:\"$wgRhinoExternalJar\" -jar \"$wgMWJSExternalJar\" -");
		$this->mIO = proc_open("$JAVA -Xbootclasspath/p:\"$wgRhinoExternalJar\" -jar \"$wgMWJSExternalJar\" -", array(
			array( 'pipe', 'w' ),
			array( 'pipe', 'r' ),
			array( 'pipe', 'r' ) // ToDo: Should we redirect stderr to file right here?
		), $this->mPipe, null, null);
		stream_set_blocking($this->mPipe[0], 0);
		stream_set_blocking($this->mPipe[1], 0);
		stream_set_blocking($this->mPipe[2], 0);
		stream_set_write_buffer($this->mPipe[0], 0);
		stream_set_write_buffer($this->mPipe[1], 0);
		stream_set_write_buffer($this->mPipe[2], 0);
		
	}
	
	function reset() {
		global $wgJSMaxTime;
		$this->mTime = time();
		$this->mEnd = $this->mTime + $wgJSMaxTime;
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

