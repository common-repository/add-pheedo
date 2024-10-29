<?php
/*
Plugin Name: Add Pheedo Plugin
Plugin URI: http://daisyolsen.com/add-pheedo/
Description: Add advertising from the Pheedo RSS advertising network and/or a customised signature or tag-line to your RSS feed(s). Enable, then configure in <a href="edit.php?page=add-pheedo/pheedo.php">Manage -> Add Pheedo</a>
Author: Daisy Olsen
Version: 1.0
Author URI: http://daisyolsen.com/
*/

/*

 Based on 'sig2feed' by Brendan Borlase - http://www.smackfoo.com/

*/


// Setup defaults if options do not exist

add_option('pheedo_data', '<a href="http://www.pheedo.com/click.phdo?x=a31b39c8097944fa995e3c3dd7d8bac7&u=%%UNIQUEID%%"><img src="http://www.pheedo.com/img.phdo?x=a31b39c8097944fa995e3c3dd7d8bac7&u=%%UNIQUEID%%" border="0"/></a>');	// Default Signature data
add_option('pheedo_sfeed', FALSE);	// Disable Add Pheedo by default


function pheedo_add_option_pages() {
	if (function_exists('add_options_page')) {
		add_management_page('Configure Add Pheedo', 'Add Pheedo', 8, __FILE__, 'pheedo_options_page');
	}		
}

function pheedo_trim_sig($sig) {
	return trim($sig, "*");
}


function pheedo_options_page() {

	if (isset($_POST['info_update'])) {	?>
		
	<div id="message" class="updated fade"><p><strong><?php 
      update_option('pheedo_data', '*' . (string)$_POST["pheedo_data"] . '*');
      update_option('pheedo_sfeed', (bool)$_POST["pheedo_sfeed"]);
			echo "Configuration updated, excellent!"; ?>
			</strong></p>
		</div>
	<?php	 } ?>

	<div class=wrap>

    <h2>Add Pheedo</h2>
	
    <p>Add Pheedo will add the necessary code, once it's been entered below, to the <strong><?php bloginfo() ?></strong> main 
        RSS feed(s), You can also enter HTML or Text to provide a custom tag-line, or direct readers back to your blog.</p>
    
    <p>To enable the Ads/signature in all feed items enable the check box in the options section of this page. Alternatively, if you wish to add post-by-post control, disable the checkbox below and insert 
        <code>&lt;!-- pheedo --&gt;</code> in any post you wish to trigger 
		Pheedo or your signature on.</p>
        
    <p><em>To check for new versions or to view more information on my work, please 
        visit the official <a href="http://daisyolsen.com/add-pheedo/">Add Pheedo plugin page</a>.</em></p>

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />
  </div>
	
	<div class=wrap>
    <fieldset class="options"> 
      
      <h3>Signature</h3>

      <textarea name="pheedo_data" cols="83" rows="6">
        <?php echo htmlspecialchars(stripslashes(pheedo_trim_sig(get_option('pheedo_data')))) ?>
      </textarea>
      <br /><br />
      <strong>Notes:</strong>
        <ul>
		  <li>This plugin allows you to include CPC and CPM advertising from the  Pheedo RSS advertising network into your feed.</li>
		  <li>Visit <a href="http://pheedo.com">Pheedo</a> to register for a publisher account.</li>
		  <li>To insert the code into your feed register your feed as a Green feed and copy the exact code provided by Pheedo into the box above.</li>
          <li>An HTML or text signature can also be entered into this box. <small>(note: use valid HTML as it will <em>not</em> be checked)</small></li>
          <li><strong>All</strong> new lines will be turned into line breaks.</li>
          <li>CSS can be used to customise the look of any HTML included in the box.</li>
          <li>An example of the Pheedo code has been provided. <small>(note: example is disabled by default, change the contents of the box and check the box below to activate)</small></li>
        </ul>	

      <strong>The following variables are available to simplify or customise the signature:</strong>
	
        <ul>
          <li>%%LOGIN%% - Login name</li>
          <li>%%FIRST%% - First name</li>
          <li>%%LAST%% - Last name</li>
          <li>%%NICK%% - Nickname</li>
          <li>%%EMAIL%% - Email address</li>
          <li>%%URL%% - Website</li>
          <li>%%DESC%% - Description/Bio</li>
        </ul>

      <fieldset class="options"> 
      <h3>Options</h3>
        <input type="checkbox" name="pheedo_sfeed" value="checkbox" <?php if (get_option('pheedo_sfeed')) echo "checked='checked'"; ?>/>
          &nbsp;&nbsp;
        <strong>Enable Pheedo Ad code and/or custom Signature and display in RSS feeds?</strong>

      <div class="submit">
        <input type="submit" name="info_update" value="<?php _e('Update options'); ?> &raquo;" />
      </div>
  </div>

  <div class='wrap'>
    <p>If you found the Add Pheedo plugin useful, please consider 
      <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=payments%40daisyolsen%2ecom&item_name=WordPress%20Plugin%20Donation&item_number=pheedo&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&bn=PP%2dDonationsBF&charset=UTF%2d8" target="_blank">making a donation</a> 
      to support it's development. Thank you!</p>
    </fieldset>
	</form>
	</div>
	
<?php
  }

  function pheedo_generate($content) {

    global $wpdb, $table_prefix, $id, $authordata;

    // Load options
      $pheedo_data = get_option('pheedo_data');
      $pheedo_sfeed = get_option('pheedo_sfeed');
	
    // Check page type
      $show_sig = FALSE;
      
      if((is_feed() || $doing_rss) && $pheedo_sfeed) {
        $show_sig = TRUE;
      }
	
      $found = strpos ($content, '<!-- pheedo -->');

      if ($found) {
        $show_sig = TRUE;
      }

      if (!$show_sig) {
        return $content;
      }

    // Get author information
      $a_login = get_the_author_login();		// %%LOGIN%%
      $a_first = get_the_author_firstname();	// %%FIRST% %
      $a_last = get_the_author_lastname();	// %%LAST%%
      $a_nick = get_the_author_nickname(); 	// %%NICK%%
      $a_email = get_the_author_email(); 		//% %EMAIL%%
      $a_url = get_the_author_url();			// %%URL%%
      $a_desc = get_the_author_description();	// %%DESC%%
  
    // Process signature
      $the_sig = stripslashes(nl2br(pheedo_trim_sig($pheedo_data)));
      $the_sig = str_replace("%%LOGIN%%", $a_login, $the_sig);
      $the_sig = str_replace("%%FIRST%%", $a_first, $the_sig);
      $the_sig = str_replace("%%LAST%%", $a_last, $the_sig);
      $the_sig = str_replace("%%NICK%%", $a_nick, $the_sig);
      $the_sig = str_replace("%%EMAIL%%", $a_email, $the_sig);
      $the_sig = str_replace("%%URL%%", $a_url, $the_sig);
      $the_sig = str_replace("%%DESC%%", $a_desc, $the_sig);
      $the_sig = str_replace("%%UNIQUEID%%",  $id, $the_sig);	

    // Look for trigger
      if ($found_trigger) { 			// If trigger found, process
        $content = str_replace('<!-- pheedo -->', $the_sig, $content);
      } else {	
        $content .= $the_sig;
    }

  return $content;

}

  add_filter('the_content', 'pheedo_generate');
  add_action('admin_menu', 'pheedo_add_option_pages');

?>