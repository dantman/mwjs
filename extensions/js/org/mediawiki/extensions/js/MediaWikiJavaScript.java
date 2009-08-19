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
package org.mediawiki.extensions.js;
import org.mozilla.javascript.*;
import java.io.*;

public class MediaWikiJavaScript {
	
	public static void main(String args[]) throws UnsupportedEncodingException {
		if ( args.length <= 0 ) {
			System.out.println("...");
			System.exit(1);
		}
		if ( args[0].equals("-") ) {
			// STDIN is input, STOUT is output, pipe closing is the closing of the instance
			
			ContextInstance ci = new ContextInstance((Reader)new BufferedReader(new InputStreamReader(System.in, "UTF-8")), System.out, System.err);
			Thread t = new Thread(ci);
			t.start();
			
		} else {
			System.out.println("Sorry only stdin/stdout support has been finished, socket server is not ready.");
		}
	}
	
}

