<?php
/*
Plugin Name: Rscrub
Plugin URI: https://github.com/joshp23/YOURLS-rscrub
Description: Referrer scrubbing swiss army knife for YOURLS
Version: 1.01
Author: Josh Panter <joshu@unfettered.net>
Author URI: https://unfettered.net
*/
// Run action manager
rscrub_mgr();

// Add the admin page
yourls_add_action( 'plugins_loaded', 'rscrub_add_page' );
function rscrub_add_page() {
	yourls_register_plugin_page( 'rscrub', 'Rscrub', 'rscrub_do_page' );
}

// Display admin page
function rscrub_do_page() {

	// Check if a form was submitted
	if( isset( $_POST['rscrub_scope'] ) ) {
	
		// Check nonce
		yourls_verify_nonce( 'rscrub' );
		
		// Process form - update option in database
		yourls_update_option( 'rscrub_scope', $_POST['rscrub_scope'] );
		if(isset($_POST['rscrub_prefix'])) yourls_update_option( 'rscrub_prefix', $_POST['rscrub_prefix'] );
		if(isset($_POST['rscrub_passthrough'])) yourls_update_option( 'rscrub_passthrough', $_POST['rscrub_passthrough'] );
		if(isset($_POST['rscrub_pass_prefix'])) yourls_update_option( 'rscrub_pass_prefix', $_POST['rscrub_pass_prefix'] );
	}
	
	// Get values from database
	$rscrub_scope = yourls_get_option( 'rscrub_scope' );
	$rscrub_prefix = yourls_get_option( 'rscrub_prefix' );
	$rscrub_passthrough = yourls_get_option( 'rscrub_passthrough' );
	$rscrub_pass_prefix = yourls_get_option( 'rscrub_pass_prefix' );
	$rscrub_pass_count = yourls_get_option( 'rscrub_pass_count' );
	$rscrub_pass_count_reset = yourls_get_option( 'rscrub_pass_count_reset' );
		
	// set prefix defaults
	if( $rscrub_prefix == null ) $rscrub_prefix = '@';
	if($rscrub_pass_prefix == null) $rscrub_pass_prefix = '+';
	if($rscrub_pass_count_reset == null) $rscrub_pass_count_reset = 'The counter has never been reset';
	
	// set variable objects and form defaults
	if ($rscrub_scope !== 'all') {
		$scope_chk = null;
		$vis_some = 'inline';
	} else {
		$scope_chk = 'checked';
		$vis_some = 'none';
	}

	if ($rscrub_passthrough !== 'true') {
		$p_chk = null;
		$vis_p = 'none';
	} else {
		$p_chk = 'checked';
		$vis_p = 'inline';
	}
	
	// Create nonce
	$nonce = yourls_create_nonce( 'rscrub' );
	
	// Prepare information for passing
	$me = $_SERVER['HTTP_HOST'];
	$rscrub_me = $me . '/' . $rscrub_pass_prefix;
	
	// rscrub.js download
	rscrub_dl($rscrub_me);
	
	// obvious
	rscrub_pass_count_reset();

	echo <<<HTML
		<link rel="stylesheet" href="/css/infos.css?v=1.7.2" type="text/css" media="screen" />
		<script src="/js/infos.js?v=1.7.2" type="text/javascript"></script>

		<div id="wrap">
			<div id="tabs">
			
				<div class="wrap_unfloat">
					<ul id="headers" class="toggle_display stat_tab">
						<li class="selected"><a href="#stat_tab_options"><h2>Rscrub Options</h2></a></li>
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
							<p><strong>Example:</strong> <code>https://$me/@V</code>  will scrub the referrer from the short url "V", where <code>https://$me/V</code> is the usual path with referrer in tact.</p>
							<p>Set a custom prefix, defaults to '@'</p>
							<p>
								<label for="rscrub_prefix">Prefix Trigger </label> 
								<input type="text" id="rscrub_prefix" name="rscrub_prefix" value="$rscrub_prefix" /> <small>Changing this value might break rscrub</small>
							</p>
						</div>
						
						<hr>
						
						<h3>Pass-through URL scrubbing</h3>
						
						<p>Rscrub can work on un-shortened urls by way of an additional prefix to the "Prefix and Shorten" syntax.</p>
						<p><strong>Example:</strong> <code>https://$me/+https://example.com</code> will scrub the referrer but will not shorten the link.</p>
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
								<input type="text" id="rscrub_pass_prefix" name="rscrub_pass_prefix" value="$rscrub_pass_prefix" /> <small>Changing this value might break rscrub</small>
							</p>
						</div>
						
						<hr>
						
						<input type="hidden" name="nonce" value="$nonce" />	
						<p><input type="submit" value="Submit" /></p>
						
					</form>
				</div>
				
				<div id="stat_tab_toolbox" class="tab">
				
					<h3>Rscrub javascript client</h3>
					
					<p>You can place a javascript function at the bottom of your page that will scrub any link that has already been written. This can be used to scrub links on pre-existing pages, or to just set it, and code on as usual.</p>
					<p>Place the following code into your html just above the <code>&lt;/body&gt;</code> tag, or just put it into your <code>footer.php</code>.
					
<pre>
&lt;script src="https://$me/io/rscrub.js"&gt;&lt;/script&gt;
&lt;script type='text/javascript'&gt;&lt;!--
	protected_links = 'example.com,$me';
	auto_anonymize();
//-->&lt;/script&gt;
</pre>
					
					<p>In the above example, any link that belongs to the <code>example.com</code> or <code>$me</code> domains will be left unscrubbed. Adjust to suit.</p>
					<p>Additionally we are calling the script <code>rscrub.js</code> from the folder <code>$me/io</code>, which needs to be created on this server.</p>
					<p>Finally, bulk scrubbing can go over ssl, or not. If your server is SSL capable, please select the option below.</p>
					
					<form class="form-horizontal" id="rscrub_dl" name="rscrub_dl" method="post">
					
						<div class="checkbox">
						  <label>
						    <input type="hidden"  id="rscrub_dl" name="rscrub_ssl"  value="no" />
						    <input type="checkbox" id="rscrub_dl" name="rscrub_ssl" value="yes"> Scrub over SSL?.
						  </label>
						</div>
						
						</br>
						
						<button type="submit" class="btn btn-primary">Download</button>
						
						<p>Please click here to download <code>rscrub.js</code> to your server. Remember to redownload this script any time you change the prefix, currently "$rscrub_pass_prefix".<p>
						
					</form>
					
					<hr>
					<hr>
					
					<h3>Example Code Toolbox</h3>
					
					<p>There are a few ways that client applications or pages may connect to rscrub. Here is a non-exhaustive list of examples of how this might be done.</p>
					<p>Note: These examples have been pre-formatted to work with this (your) site, and are therefore copypasta ready.</p>
					
					<h4>HTML Link</h4>
					
					<p>If you only need to scrub a single link, just wrap the "prefix + url" in html and post it on your site.</p>
					
<pre>
&lt;a href='https://$rscrub_me&#173;https://www.whatismyreferer.com'&gt;Your Text Here&lt;/a&gt;
</pre>	
				
					<h4>PHP Function</h4>
					
					<p>PHP can be used to create a link scrubbing shorthand.</p>
					<p>First place this function on your page.</p>	
									
<pre>
&lt;?php
	function hideref(&#36;strUrl='', &#36;text='') {
		&#36;a= "https://$rscrub_me&#173;".&#36;strUrl;
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
		return "https://$rscrub_me&#173;"+escape(strUrl);
	}
&lt;/script&gt;
</pre>
					
					<p>Here's the "shorthand"</p>
					
<pre>
&lt;a href="https://www.whatismyreferer.com" onclick="window.open(href=hideref(this.href)); return false;">JS Link Text&lt;/a&gt;
</pre>

				</div>
				
				<div id="stat_tab_pc" class="tab">
					<p>Current total number of rscrub pass-through events: <strong>$rscrub_pass_count</strong> </p>
					<p>Date of last counter reset: <strong>$rscrub_pass_count_reset, UTC</strong> </p>
					
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
function rscrub_dl($rscrub_me) {


	// Was the form submitted?
	if( isset( $_POST['rscrub_ssl'] ) ) {
	
	
		// protocol identifier: ssl?
		if( $_POST['rscrub_ssl'] !== 'no' ) {
			$pi = 'https://';
		} else {
			$pi ='http://';
		}
		
		
		// get the dist file and custom strings ready
		$rscrub_var = $pi . $rscrub_me;
		$a = file_get_contents( dirname( __FILE__ )."/inc/rscrub-dist.js");
		$b = substr_replace($a, $rscrub_var, '13', 0);
		
		
		// timestamp the script output
		$now = date( 'M d, Y g:i A');
		$stamp = 'This script was auto-generated by rscrub for YOURLS on ' . $now . ' UTC';
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

// Reset the counter
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

	// change the location on the parent document from within an iframe
	// works with all major browsers as of 2015-05-12.
	echo "<iframe style=\"display:none\" src=\"javascript:parent.location.replace('".$url."'+(parent.location.hash||''))\">";
	
	// backup method (results may vary)
	// May scrub under SSL, will typically show the LAST redirect url (the yourls url).
	// 1 second delay so the iframe gets a chance first.
	echo "<meta http-equiv=\"refresh\" content=\"1; url=".$url."\" />";
}
?>
