<?php
/*
Plugin Name: Rscrub
Plugin URI: https://github.com/joshp23/YOURLS-rscrub
Description: Referrer scrubbing swiss army knife for YOURLS
Version: 1.2.2
Author: Josh Panter <joshu@unfettered.net>
Author URI: https://unfettered.net
*/
// rscrub.js dl
yourls_add_action( 'pre_html_logo', 'rscrub_dl' );
// Run action manager
rscrub_mgr();
// Add the admin page
yourls_add_action( 'plugins_loaded', 'rscrub_add_page' );
function rscrub_add_page() {
	yourls_register_plugin_page( 'rscrub', 'Rscrub', 'rscrub_do_page' );
}
// Maybe inject js/css for admin page
yourls_add_action( 'html_head', 'rscrub_head' );
function rscrub_head() {
	if (defined('YOURLS_JP23_HEAD_FILES') == false ) {
		define( 'YOURLS_JP23_HEAD_FILES', true );
		$home = YOURLS_SITE;
		echo "\n<! --------------------------JP23_HEAD_FILES Start-------------------------- >\n";
		echo "<link rel=\"stylesheet\" href=\"".$home."/css/infos.css?v=".YOURLS_VERSION."\" type=\"text/css\" media=\"screen\" />\n";
		echo "<script src=\"".$home."/js/infos.js?v=".YOURLS_VERSION."\" type=\"text/javascript\"></script>\n";
		echo "<! --------------------------JP23_HEAD_FILES END---------------------------- >\n";
	}
}
// Display admin page
function rscrub_do_page() {

	// Check for config form submissions
	rscrub_primary_ops();
	rscrub_pass_sub_config();
	
	$o = rscrub_options();
	
	// set variable objects and form defaults
	if ($o[0] !== 'all') {
		$scope_chk = null;
		$vis_some = 'inline';
	} else {
		$scope_chk = 'checked';
		$vis_some = 'none';
	}

	if ($o[2] !== 'true') {
		$p_chk = null;
		$vis_p = 'none';
	} else {
		$p_chk = 'checked';
		$vis_p = 'inline';
	}
	
	if ($o[6] !== 'true') {
		$sub_chk = null;
	} else {
		$sub_chk = 'checked';
	}
	
	// Create nonce
	$nonce = yourls_create_nonce( 'rscrub' );
	
	// Prepare information for passing and forms
	$h = $_SERVER['HTTP_HOST'];
	$x = explode('.', $h);
	
	if ($o[6] !== 'true' ) $url = $h . '/' . $o[3];
	else $url = $o[7] . '.' . $h . '/';
	
	rscrub_pass_count_reset();

	$file = dirname( __FILE__ )."/plugin.php";
	$data = yourls_get_plugin_data( $file );
	$v = $data['Version'];
	echo <<<HTML
		<div id="wrap">
			<div id="tabs">
			
				<div class="wrap_unfloat">
					<ul id="headers" class="toggle_display stat_tab">
						<li class="selected"><a href="#stat_tab_options"><h2>Rscrub Options</h2></a></li>
						<li class="selected"><a href="#stat_tab_sub_options"><h2>Subdomain Scrubbing</h2></a></li>
						<li style="display:$vis_p;"><a href="#stat_tab_toolbox"><h2>Tools</h2></a></li>
						<li style="display:$vis_p;"><a href="#stat_tab_pc"><h2>Pass-through Counter</h2></a></li>
					</ul>
				</div>
					
				<div id="stat_tab_options" class="tab">
				
					<h2>Rscrub Options</h2>
					
					<h3>Short URL scrubbing</h3>
					
					<p>Rscrub's default behavior is to only scrub referrer information when requested. Optionally, rscrub can act on all links.</p>

					<form method="post">
		
						<div class="checkbox">
						  <label>
						    <input type="hidden" name="rscrub_scope" value="some" />
						    <input name="rscrub_scope" type="checkbox" value="all" $scope_chk > Universal scrubbing for short urls?
						  </label>
						</div>
			
						<div style="display:$vis_some;">	
							<p>Contitional scrubbing uses a prefix with the short url alias.</p> 
							<p><strong>Example:</strong> <code>https://$h/$o[1]&#173;V</code>  will scrub the referrer from the short url "V", where <code>https://$h/V</code> is the usual path with referrer in tact.</p>
							<p>Set a custom prefix, defaults to '@'</p>
							<p>
								<label for="rscrub_prefix">Prefix Trigger </label> 
								<input type="text" id="rscrub_prefix" name="rscrub_prefix" value="$o[1]" /> <small>Changing this value might break rscrub</small>
							</p>
						</div>
						
						<hr>
						
						<h3>Pass-through URL scrubbing</h3>
						
						<p>Rscrub can work on un-shortened urls by way of an additional prefix to the "Prefix and Shorten" syntax.</p>
						<p><strong>Example:</strong> <code>https://$h/$o[3]&#173;https://example.com</code> will scrub the referrer but will not shorten the link.</p>
						<div class="checkbox">
						  <label>
						    <input type="hidden" name="rscrub_passthrough" value="false" />
						    <input name="rscrub_passthrough" type="checkbox" value="true" $p_chk > Allow rscrub pass-through?
						  </label>
						</div>
						
						<div style="display:$vis_p;">
							<p>Set a custom prefix, defaults to '+'</p>
							<p>
								<label for="rscrub_pass_prefix">Prefix Trigger </label>
								<input type="text" id="rscrub_pass_prefix" name="rscrub_pass_prefix" value="$o[3]" /> <small>Changing this value might break rscrub</small>
							</p>
						</div>
						
						<hr>
						
						<input type="hidden" name="nonce" value="$nonce" />	
						<p><input type="submit" value="Submit" /></p>
						
					</form>
					<p><strong>Note:</strong> There is a problem of having no preview and no identifying information appear when posting scrubbed links to certain social media sites. This problem can be addressed: if the Snapshot Visual Preview plugin is installed, properly configured, and enabled, then Rscrub will provide a snapshot preview and the proper headers to these sites automatically. Please see the Snapshot project page on <a href="https://github.com/joshp23/YOURLS-Snapshot" target="_blank">github</a> for more information.</p>
				</div>
				
				<div id="stat_tab_sub_options" class="tab">
				
					<p>Rscrub can use subdomains to scrub urls, this requires access to your Virtual Host config files and important additions to the YOURLS <code>.htaccess</code> file. When using this option the prefix settings are still relevant.</p>
					
					<div style="display:$vis_p;">
					
						<h3>Pass-through URL scrubbing: Subdomain Settings</h3>
						
						<p>If you are using a subdomain for pass-through scrubbing of long url's, please set the options below. Prefix options are still relevant.</p>
						<p>Note that this will change many values in the Tools dialogue, and it will make some important changes to <code>rscrub.js</code>.</p>
					
						<form class="form-horizontal" id="rscrub_pass_subdomain" name="rscrub_pass_subdomain" method="post">
							<div class="checkbox">
							  <label>
							    <input type="hidden" name="rscrub_pass_subdomain_do" value="false" />
							    <input name="rscrub_pass_subdomain_do" type="checkbox" value="true" $sub_chk > Use rscrub pass-through subdomain?
							  </label>
							</div>
							
							<p>
								<label for="rscrub_pass_subdomain_is">Pass-through Subdomain </label> 
								<input type="text" id="rscrub_pass_subdomain_is" name="rscrub_pass_subdomain_is" value="$o[7]" /> The default long url scrubbing subdomain is <code>anon.$h</code>.
							</p>
							
							<input type="hidden" name="nonce" value="$nonce" />	
							<p><input type="submit" value="Submit" /></p>
							
						</form>
						<hr>
					</div>
					
					<h3>Setting up subdomain scrubbing</h3>
					
					This will explain how to set up subdomain scrubbing on an Apache webserver.
					
					<h4>Part one: The virtual host</h4>
					
					<p>To use a subdomain for a prefix function, we first need to add it in to the YOURLS virtual host conf file in Apache using the <code>ServerAlias</code> directive. Your conf file should look something like the following; it should be easy to notice that the subdomain "<code>a</code>" is being used for our short url function.</p>
					
<pre>
&#60;VirtualHost *:80&#62;

	ServerName $h
	
	<strong>ServerAlias a.$h
	<span style="display:$vis_p;">ServerAlias $o[7].$h</span></strong>
	
	DocumentRoot /var/www/YOURLS/
	&#60;Directory /var/www/YOURLS/&#62;
		Options -Indexes +FollowSymLinks +MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
	&#60;/Directory&#62;

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel info
	ErrorLog /var/log/apache2/error.log
	CustomLog /var/log/apache2/access.log combined
	
&#60;/VirtualHost&#62;
</pre>
					<p>You might find your Apache default virtual host config file at <code>/etc/apache2/sites-available/000-default.conf</code>.</p>
					<p>Once you have added that in, make sure to save the file and restart Apache.</p>
					
					<h4>Part Two: YOURLS .htaccess file</h4>
					
					<p>The following rules need to be added in to the very top of the YOURLS .htaccess file. They make use of both <code>mod_rewrite</code> and <code>mod_proxy</code>, so both of these modules need to be enabled on your server.
					
<pre>
RewriteEngine On

# RSCRUB - SHORT URL
RewriteCond %{HTTP_HOST} ^<strong>a</strong>\.(<strong>$x[0]</strong>\.<strong>$x[1]</strong>)$ [NC]
RewriteRule ^/?([a-zA-Z0-9]+)$ https://%1/<strong>$o[1]</strong>$1 [P]

<span style="display:$vis_p;"># RSCRUB - LONG URL
RewriteCond %{HTTP_HOST} ^<strong>$o[7]</strong>\.(<strong>$x[0]</strong>\.<strong>$x[1]</strong>)$ [NC]
RewriteRule ^/?([a-zA-Z0-9]+)$ https://%1/<strong>$o[3]</strong>$1 [P]</span>
</pre>

				<p>These rules have been generated using this site's current configuration. Any changes to your setup will necessitate an alteration of these rules in your system.</p>
				<p><strong>NOTE:</strong> If you are using SSL on your site, which is reccomended for this module, make certain to set <code>SSLProxyEngine on</code> in your virtual host, otherwise these proxies will fail. If not, you will have to adjust the above code accordingly.</p>

				</div>
				
				<div id="stat_tab_toolbox" class="tab">
				
					<h3>Rscrub javascript client</h3>
					
					<p>You can enforce scrubbing for every link on a page or site. This solution has two parts.</p>
					
					<h4>Part One: Client Side</h4>
					
					<p>Place this in your html just above the <code>&lt;/body&gt;</code> tag. If applicable, it can be put into <code>footer.php</code>. <strong>Note:</strong> Any link that appears after this code is called will not be scrubbed.</p>
					
<pre>
&lt;script src="https://$h/io/rscrub.js?v=$v"&gt;&lt;/script&gt;
&lt;script type='text/javascript'&gt;&lt;!--
	protected_links = 'example.com,$h';
	auto_anonymize();
//-->&lt;/script&gt;
</pre>
					
					<p>In the above example, any link that belongs to the <code>example.com</code> or <code>$h</code> domains will be left unscrubbed. Adjust to suit.</p>
					
					<h4>Part Two: Server Side</h4>
					
					<p>The client side code calls the script <code>rscrub.js</code>, which first has to be configured here and then downloaded to your server. The above example places it at <code>$h/io</code>, so the foler <code>io</code> needs to be created in the YOURLS root folder.</p>
					<p>Bulk scrubbing can go over ssl, or not. If your server is SSL capable, please select the option below.</p>
					
					<form class="form-horizontal" id="rscrub_dl" name="rscrub_dl" method="post">
					
						<div class="checkbox">
						  <label>
						    <input type="hidden"  id="rscrub_dl" name="rscrub_ssl"  value="no" />
						    <input type="checkbox" id="rscrub_dl" name="rscrub_ssl" value="yes"> Scrub over SSL?
						  </label>
						  <small>This will reset after download</small>
						</div>
						
						</br>
						<button type="submit" class="btn btn-primary">Download</button>
						
						<p>Click above to download <code>rscrub.js</code>. Remember to grab a fresh copy of this script any time you change your settings. Currently the prefix is set to "<code>$o[3]</code>", and your subdomain config is set to "<code>$o[6]</code>" for the subdomain "<code>$o[7].$h</code>".<p>
						
					</form>
					
					<hr>
					<hr>
					
					<h3>Example Code Toolbox</h3>
					
					<p>There are a few ways that client applications or pages may connect to rscrub. Here is a non-exhaustive list of examples of how this might be done.</p>
					<p>Note: These examples have been pre-formatted to work with this (your) site, and are therefore copypasta ready.</p>
					
					<h4>HTML Link</h4>
					
					<p>If you only need to scrub a single link, just wrap the "prefix + url" in html and post it on your site.</p>
					
<pre>
&lt;a href='https://$url&#173;https://www.whatismyreferer.com'&gt;Your Text Here&lt;/a&gt;
</pre>	
				
					<h4>PHP Function</h4>
					
					<p>PHP can be used to create a link scrubbing shorthand.</p>
					<p>First place this function on your page.</p>	
									
<pre>
&lt;?php
	function hideref(&#36;strUrl='', &#36;text='') {
		&#36;a= "https://$url&#173;".&#36;strUrl;
		&#36;b= "&lt;a href='&#36;a' target='_blank'&gt;&#36;text&lt;/a&gt;";
		return &#36;b;
} 
?&gt;
</pre>

					<p>Then just use this shorthand to scrub links.</p>

<pre>
&lt;?= hideref('https://www.whatismyreferer.com', 'PHP Link Text') ?&gt;
</pre>

					<h4>JS Function</h4>
					
					<p>Similarly, we can use a javascript function to shorten links.</p>
					<p>Here the function must appear above whatever link you want to use it on.</p>

<pre>
&lt;script language="JavaScript"&gt;
	function hideref(strUrl){
		return "https://$url&#173;"+escape(strUrl);
	}
&lt;/script&gt;
</pre>
					
					<p>Here's the "shorthand"</p>
					
<pre>
&lt;a href="https://www.whatismyreferer.com" onclick="window.open(href=hideref(this.href)); return false;">JS Link Text&lt;/a&gt;
</pre>

				</div>
				
				<div id="stat_tab_pc" class="tab">
					<p>Current total number of rscrub pass-through events: <strong>$o[4]</strong> </p>
					<p>Date of last counter reset: <strong>$o[5], UTC</strong> </p>
					
					<form method="post">
						<div class="checkbox">
						  <label>
							<input name="rscrub_ptc_reset" type="hidden" value="no" />
							<input name="rscrub_ptc_reset" type="checkbox" value="yes"> Do you want to reset the counter?
						  </label>
						</div>
					<input type="hidden" name="nonce" value="$nonce" />
					<p><input type="submit" value="Reset" /></p>
				</form>
				</div>
				
			</div>
		</div>
HTML;
}
// options manager
function rscrub_options() {
	// Get values from database
	$rscrub_scope = yourls_get_option( 'rscrub_scope' );
	$rscrub_prefix = yourls_get_option( 'rscrub_prefix' );
	$rscrub_passthrough = yourls_get_option( 'rscrub_passthrough' );
	$rscrub_pass_prefix = yourls_get_option( 'rscrub_pass_prefix' );
	$rscrub_pass_count = yourls_get_option( 'rscrub_pass_count' );
	$rscrub_pass_count_reset = yourls_get_option( 'rscrub_pass_count_reset' );
	$rscrub_pass_subdomain_do = yourls_get_option( 'rscrub_pass_subdomain_do' );
	$rscrub_pass_subdomain_is = yourls_get_option( 'rscrub_pass_subdomain_is' );
		
	// set some defaults
	if($rscrub_prefix == null ) $rscrub_prefix = '@';
	if($rscrub_pass_prefix == null) $rscrub_pass_prefix = '+';
	if($rscrub_pass_subdomain_is == null) $rscrub_pass_subdomain_is = 'anon';
	if($rscrub_pass_subdomain_do == null) $rscrub_pass_subdomain_do = 'false';
	if($rscrub_pass_count_reset == null) $rscrub_pass_count_reset = 'The counter has never been reset';

	return array(
		$rscrub_scope,				// opt[0]
		$rscrub_prefix,				// opt[1]
		$rscrub_passthrough,		// opt[2]
		$rscrub_pass_prefix,		// opt[3]
		$rscrub_pass_count,			// opt[4]
		$rscrub_pass_count_reset,	// opt[5]
		$rscrub_pass_subdomain_do,	// opt[6]
		$rscrub_pass_subdomain_is	// opt[7]
	);
}
/*
	Forms
*/
// Primary Settings
function rscrub_primary_ops() {

	// Check if the form was submitted
	if( isset( $_POST['rscrub_scope'] ) ) {
	
		// Check nonce
		yourls_verify_nonce( 'rscrub' );
		
		// Process form - update option in database
		yourls_update_option( 'rscrub_scope', $_POST['rscrub_scope'] );
		if(isset($_POST['rscrub_prefix'])) yourls_update_option( 'rscrub_prefix', $_POST['rscrub_prefix'] );
		if(isset($_POST['rscrub_passthrough'])) yourls_update_option( 'rscrub_passthrough', $_POST['rscrub_passthrough'] );
		if(isset($_POST['rscrub_pass_prefix'])) yourls_update_option( 'rscrub_pass_prefix', $_POST['rscrub_pass_prefix'] );
		
		echo '<font color="green">Rscrub options saved. Have a nice day!</font>';
	}
}
// Subdomain Setting for pass through script
function rscrub_pass_sub_config() {

	// did the passthrough form get submitted?
	if( isset( $_POST['rscrub_pass_subdomain_do'] ) ) {
		
		// Check nonce
		yourls_verify_nonce( 'rscrub' );
		
		// update the options
		yourls_update_option( 'rscrub_pass_subdomain_do', $_POST['rscrub_pass_subdomain_do'] );
		if(isset($_POST['rscrub_pass_subdomain_is'])) 
			yourls_update_option( 'rscrub_pass_subdomain_is', $_POST['rscrub_pass_subdomain_is'] );

		echo '<font color="green">Pass through subdomain options saved. Have a nice day!</font>';
	}
}
// rscrub.js download manager
function rscrub_dl() {

	// Was the form submitted?
	if( isset( $_POST['rscrub_ssl'] ) ) {

		$o = rscrub_options();
		$h = $_SERVER['HTTP_HOST'];
		if ($o[6] !== 'true') $url = $h.'/'.$o[3];
		else $url = $o[7].'.'.$h.'/';

		// protocol identifier: ssl?
		if( $_POST['rscrub_ssl'] !== 'no' )
			$pi = 'https://';
		else
			$pi ='http://';
		
		// get the dist file and custom strings ready
		$var = $pi.$url;
		$a = file_get_contents( dirname( __FILE__ )."/inc/rscrub-dist.js");
		$b = substr_replace($a, $var, '13', 0);
		
		// identify this with a version string
		$p = dirname( __FILE__ )."/plugin.php";
		$d = yourls_get_plugin_data( $p );
		$v = $d['Version'];
		$stamp = 'v'.$v;
		$c = substr_replace($b, $stamp, '3', 0);
		
		// force a download
		header('Content-Disposition: attachment; filename="rscrub.js"');
		header('Content-Type: application/javascript');
		header('Content-Length: ' . strlen($c));
		header('Connection: close');
		
		// clear any printed data and download output
		ob_end_clean();
		echo $c;
		
		die();
	}
}
// Reset the pass-through counter
function rscrub_pass_count_reset() {

	// did the reset form get submitted?
	if( isset( $_POST['rscrub_ptc_reset'] ) ) {
	
		// was the checkbox ticked?
		if( $_POST['rscrub_ptc_reset'] !== 'no' ) {
		
		// Check nonce
		yourls_verify_nonce( 'rscrub' );
		
		// update the reset option and timestamp this event
		yourls_update_option( 'rscrub_pass_count', 0 );
		$now = date( 'M d, Y g:i A');
		yourls_update_option( 'rscrub_pass_count_reset', $now );

		echo '<font color="green">Pass-through event counter reset. Have a nice day!</font>';
		}
	}
}
/*
	Scrubbing engine
*/
// Action Manager
function rscrub_mgr() {

	// Scrub all or some?
	$rscrub_scope = yourls_get_option( 'rscrub_scope' );
	if ( $rscrub_scope == 'all' ) {
	
		// Pre redirect - hide referrer on ALL urls
		yourls_add_action( 'pre_redirect', 'rscrub_pre_redirect' );
	
	} else {
	
		// Loader failed - hide referrer on urls with prefix
		yourls_add_action( 'loader_failed', 'rscrub_loader_failed_0' );
	}
	
	// scrub ref w/out shorten
	$rscrub_passthrough = yourls_get_option( 'rscrub_passthrough' );
	if($rscrub_passthrough == 'true') {
		yourls_add_action( 'loader_failed', 'rscrub_lf_passthrough' );
	} 	
}

// universal scrubbing for short url's
function rscrub_pre_redirect( $args ) {
	$url = $args[0];
	$code = $args[1];
	rscrub($url);
	// Now die so the normal redirect is interrupted
	die();
}
// conditional scrubbing for short url's - default action
function rscrub_loader_failed_0( $args ) {

	// get the prefix & set a default value
	$rscrub_prefix = yourls_get_option( 'rscrub_prefix' );
	if( $rscrub_prefix == null ) $rscrub_prefix = '@';
	
	// look for the prefix on loader failures
	if( preg_match( '!^'. $rscrub_prefix .'(.*)!', $args[0], $matches ) ) {
	
		// match found: prepare url for scrubbing
		$keyword = yourls_sanitize_keyword( $matches[1] );
		require_once( dirname( __FILE__ ) . '/../../../includes/load-yourls.php' );
		$url = yourls_get_keyword_longurl( $keyword );
		
		// send url to scrubber
		rscrub($url);

		exit;
	}	
}
// pass-through scrubbing
function rscrub_lf_passthrough( $args ) {

	//get the prefix & set a default value
	$rscrub_pass_prefix = yourls_get_option( 'rscrub_pass_prefix' );
	if($rscrub_pass_prefix == null) $rscrub_pass_prefix = '+';
	
	// look for the prefix on loader failures
	if( preg_match( '!/' . $rscrub_pass_prefix . '(.*)!', $args[0], $matches ) ) {
	
		// match found: update counter
		$rscrub_pass_count = yourls_get_option( 'rscrub_pass_count' );
		$rscrub_pass_count = $rscrub_pass_count + 1;
		yourls_update_option( 'rscrub_pass_count', $rscrub_pass_count );
		
		// prep url
		$url = $matches[1];
		$url = yourls_sanitize_url($url);
		
		// send url to scrubber
		rscrub($url);
		exit;
	}
}
// actual scrubbing
function rscrub( $url ) {

	// quick check for social share preview
	$keys = yourls_get_longurl_keywords( $url );
	if($keys == null) {
		$keyword = $keys;
	} else {
		$keyword = $keys[0];
	}
	rscrub_social_chk( $keyword, $url );
	
	// change the location on the parent document from within an iframe
	// works with all major browsers as of 2015-05-12	
	echo "<iframe style=\"display:none\" src=\"javascript:parent.location.replace('".$url."'+(parent.location.hash||''))\">";

	// backup method (results may vary)
	// May scrub under SSL, will typically show the LAST redirect url (the yourls url).
	// 1 second delay so the iframe gets a chance first.
	echo "<meta http-equiv=\"refresh\" content=\"1; url=".$url."\" />";
}
// Check for social link shares
function rscrub_social_chk( $keyword, $url ) {

	if ( 	
		// Here are some user agents to look out for. Add as neccessary.
		// please open an issue (or a pull request) to have more added: https://github.com/joshp23/YOURLS-rscrub/issues
		strpos($_SERVER["HTTP_USER_AGENT"], "facebookexternalhit") 	!== false ||          
    		strpos($_SERVER["HTTP_USER_AGENT"], "Facebot") 			!== false ||          
    		strpos($_SERVER["HTTP_USER_AGENT"], "Twitterbot") 		!== false ||          
    		strpos($_SERVER["HTTP_USER_AGENT"], "Tumblr") 			!== false ||          
    		strpos($_SERVER["HTTP_USER_AGENT"], "Google") 			!== false
												) {
		// Here is the command to be called if there is a match.
		// TODO: add more options here. Please submit an issue or pull request.
		if((yourls_is_active_plugin('snapshot/plugin.php')) !== false) 
			rscrub_snapshpot_preview( $keyword, $url );
	}	
}
// snapshot integration (https://github.com/joshp23/YOURLS-Snapshot)
function rscrub_snapshpot_preview( $keyword, $url ) {

	$title  = yourls_get_keyword_title( $keyword );
	
	$base 	= YOURLS_SITE;
	$id 	= 'snapshot';
	$fn 	= snapshot_request($keyword, $url);
	
	if($fn == 'alt') {

		$id = 'snapshot-alt';
		$fn = array(
			'sorry.png',
			'420'
		);
	}
	
	$now = round(time()/60);
	$key = md5($now . $id);
	
	$img = $base.'/srv/?id='.$id.'&key='.$key.'&fn='.$fn[0];
	$preview = 	'<html>
				<head>
					<meta property="og:image" content="'.$img.'" />
					<meta property="og:title" content="'.$title.'" />
				</head>
		
				<body>
					<img src="'.$img.'" width="800" />
				</body>
			</html>';
	
	echo $preview;
	die();
}
?>
