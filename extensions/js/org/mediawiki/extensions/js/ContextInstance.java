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

public class ContextInstance implements Runnable {
	
	protected Reader in;
	protected PrintWriter out;
	protected PrintWriter err;
	
	public ContextInstance(Reader in, PrintStream out, PrintStream err) {
		this.in = in;
		this.out = out;
		this.err = err;
	}
	
	public void run() {
		Global global = new Global();
		try {
			Context cx = Context.enter();
			
		} finally {
			Context.exit();
		}
	}
	
	/* Run a script embedded into the jar */
	protected Object quickRunScript( Context cs, ScriptableObject scope, Script fileName ) {
		try {
			InputStream is = ContextInstance.class.getResourceAsStream( fileName );
			BufferedReader in = new BufferedReader(new InputStreamReader(is, "UTF-8"));
			return cx.evaluateReader( scope, in, fileName, 1, null );
		}
		catch( IOException e ) { /*this.out.println("Failed to exec script "+filename);*/ }
		catch( NullPointerException e ) { /*this.out.println("Failed to exec script "+filename);*/ }
		return null;
	}
	
}
