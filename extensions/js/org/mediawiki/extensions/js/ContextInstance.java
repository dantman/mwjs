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
import java.util.Arrays;

public class ContextInstance implements Runnable {
	
	protected Reader in;
	protected PrintStream out;
	protected PrintStream err;
	
	public ContextInstance(Reader in, PrintStream out, PrintStream err) {
		this.in = in;
		this.out = out;
		this.err = err;
	}
	
	public void run() {
		Global global = new Global();
		try {
			Context cx = Context.enter();
			//cx.setLanguageVersion(Context.VERSION_);
			global.init(cx);
			quickRunScript( cx, global, "json2.js" );
			writeMessage(new String[] {"output", "foo"});
			try {
				while (true) {
					String[] message = readMessage();
					if ( message == null )
						return; // abort
					String action = message[0];
					if ( action.equals("close") ) {
						return; // abort = close
					} if( action.equals("exec") ) {
						// Simply exec a chunk of code
						String code = message[1];
						
						writeMessage(new String[] { "output", "test" });
					} if ( action.equals("eval") ) {
						// Evaluate and return output from a chunk of code
					} else {
						// Unknown message
						return; // abort
					}
				}
			} catch( IOException e ) { return; } // abort
			
		} finally {
			Context.exit();
		}
	}
	
	private String[] readMessage() throws IOException {
		String[] message = null;
		char[] chars = new char[256];
		int off = 0;
		for(;;++off) {
			if ( off == chars.length )
				chars = Arrays.copyOf(chars, chars.length + 256);
			int l = this.in.read(chars, off, 1);
			if ( l != 1 )
				throw new IOException();
			if ( chars[off] == '\0' ) {
				if ( off == 0 ) {
					// null char following a null char, finish message
					break;
				} else {
					String part = new String(chars, 0, off-1);
					if ( message == null ) {
						message = new String[] { part };
					} else {
						message = Arrays.copyOf(message, message.length+1);
						message[message.length-1] = part;
					}
					chars = new char[256];
					off = 0;
				}
			}
		}
		return message;
	}
	
	private void writeMessage(String[] message) {
		for(int i=0; i!=message.length; ++i) {
			this.out.print(message[i]);
			this.out.print('\0');
		}
		this.out.print('\0');
		this.out.flush();
	}
	
	/* Run a script embedded into the jar */
	protected Object quickRunScript( Context cx, ScriptableObject scope, String fileName ) {
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
