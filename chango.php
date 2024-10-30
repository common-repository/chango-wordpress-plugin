<?php
/*
  Plugin Name: Chango
  Plugin URI: http://www.chango.com/wp
  Description: Chango Wordpress Plugin: Insert a Chango Ad-Unit while typing your content, and make money effortlessly.
  Version: 1.0.0
  Author: Chango
  Author URI: http://www.chango.com
*/

/*  Copyright 2009  Chango, Inc.  (email : ahmed@chango.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

  ////////////////////////////////////////////////////
  /////////////// Activation Functions ///////////////
  ////////////////////////////////////////////////////
  register_activation_hook(__FILE__, "chango_add_options");
  
  function chango_add_options() {
    add_option("chango_ad_placement");
    add_option("chango_js_code");
    add_option("chango_show_on_homepage", "on");
    add_option("chango_number_of_paragraphs");
    add_option("chango_paragraphs_count_from");
  }
  
  ////////////////////////////////////////////////////
  ////////////// Deactivation Functions //////////////
  ////////////////////////////////////////////////////
  register_deactivation_hook(__FILE__, "chango_remove_options");
  
  function chango_remove_options() {
    delete_option("chango_ad_placement");
    delete_option("chango_js_code");
    delete_option("chango_show_on_homepage");
    delete_option("chango_number_of_paragraphs");
    delete_option("chango_paragraphs_count_from");
  }

  ////////////////////////////////////////////////////
  ////////////// Ad-Unit Insertion Code //////////////
  ////////////////////////////////////////////////////
  function insert_ad_unit($content) {
    $script = get_option("chango_js_code");
    
    if(display_ad()) {
      if(get_option("chango_ad_placement") == "top") {
        $content = $script . $content;
      } elseif(get_option("chango_ad_placement") == "bottom") {
        $content = $content . $script;
      } else {
        // After
        $content_split = split("<p>", $content);
        if(get_option("chango_paragraphs_count_from") == "top") {
          $position = min(get_option("chango_number_of_paragraphs"), sizeof($content_split));
        } else {
          $position = max(0, sizeof($content_split) - get_option("chango_number_of_paragraphs"));
        }
        array_splice($content_split, $position, 0, $script);
        $content = join("<p>", $content_split);
      }
    }
    
    return $content;
  }
  
  function display_ad() {
    // Explanation:
    // Never insert if feed page
    // Always insert if not feed page and not home page
    // Insert if if not feed page, is home page, and the show on homepage option is set
    return (!is_feed() && (!is_home() || (is_home() && get_option("chango_show_on_homepage"))));
  }
  
  add_filter("the_content", "insert_ad_unit");

  ////////////////////////////////////////////////////
  ////////////////// Add Admin Page //////////////////
  ////////////////////////////////////////////////////
  add_action('admin_menu', 'chango_options_setup');
  
  function chango_options_setup() {
   add_options_page("Chango Options", "Chango Options", 8, __FILE__, "chango_options_page"); 
  }
  
  function chango_options_page() {
    ?>
      <link rel='stylesheet' href='<?php print get_bloginfo('wpurl') ?>/wp-content/plugins/chango/chango.css' type='text/css' media='all' />
      
      <div id="chango">
        <div id="postaiosp" class="postbox">
          <h3><img src="http://www.chango.com/images/chango_logo.png"/></h3>
          <div class="inside">
            <div class="postbox">
              <h2>Instructions:
              <ul style="list-style-type: decimal; margin: 19px 0 0 40px">
                <li>Login to your account at <a target="_blank" href="https://chango.com/login">https://chango.com/login</a></li>
                <li>Click on Ad Setup</li>
                <li>Click on Chango Ad Units</li>
                <li>Select site from the drop down</li>
                <li>Select the ad size from the drop down</li>
                <li>Configure any additional options</li>
                <li>Copy the code and paste in the box below</li>
              </ul>
            </div>
            <div style="margin-left: 20px;">      
              <div class="wrap">
                <form method="post" action="options.php">
                  <?php wp_nonce_field("update-options"); ?>
          
                  <table cellspacing=20>
                    <tr>
                      <td>Chango JS:</td>
                      <td>
                        <textarea rows="7" cols="40" name="chango_js_code"><?php print get_option("chango_js_code"); ?></textarea>
                      </td>
                    </tr>
                    <tr>
                      <td>Chango Ad Placement</td>
                      <td>
                        <select name="chango_ad_placement" onchange="changoAdPlacementOptions()" id="chango_ad_placement" style="float: left;">
                          <?php print output_option_tag("top",    "chango_ad_placement", "Top of post"); ?>
                          <?php print output_option_tag("bottom", "chango_ad_placement", "Bottom of post"); ?>
                          <?php print output_option_tag("after",  "chango_ad_placement", "After"); ?>
                        </select>
                        <span id="chango_number_of_paragraphs" style="display: none; float: left;">
                          <select name="chango_number_of_paragraphs">
                            <?php print output_option_tag("1",  "chango_number_of_paragraphs", "1"); ?>
                            <?php print output_option_tag("2",  "chango_number_of_paragraphs", "2"); ?>
                            <?php print output_option_tag("3",  "chango_number_of_paragraphs", "3"); ?>
                            <?php print output_option_tag("4",  "chango_number_of_paragraphs", "4"); ?>
                          </select>
                          paragraph(s) from
                          <select name="chango_paragraphs_count_from">
                            <?php print output_option_tag("top",    "chango_paragraphs_count_from", "top"); ?>
                            <?php print output_option_tag("bottom", "chango_paragraphs_count_from", "bottom"); ?>
                          </select>
                        </span>
                      </td>
                    </tr>
                    <tr>
                      <td>Show on homepage:</td>
                      <td>
                        <input type="checkbox" name="chango_show_on_homepage" <?php get_option("chango_show_on_homepage") ? print "checked" : "" ?>>  Show on homepage
                      </td>
                    </tr>
                  </table>
          
                  <input type="hidden" name="action" value="update" />
                  <input type="hidden" name="page_options" value="chango_js_code,chango_show_on_homepage,chango_ad_placement,chango_number_of_paragraphs,chango_paragraphs_count_from" />
          
                  <p class="submit">
                    <input type="submit" name="Submit" value="<?php _e("Update Options >>"); ?>" />
                  </p>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <script type="text/javascript">
        //<![CDATA[
        changoAdPlacementOptions();
        //]]>
      </script>
    <?php
  }

  // Add JS Functions
  add_action('admin_print_scripts', 'chango_js_functions');
  function chango_js_functions() {
    ?>
      <script type="text/javascript">
      //<![CDATA[
      function changoAdPlacementOptions() {
        placementOptions = document.getElementById("chango_ad_placement");
        
        if(placementOptions.value == 'after') {
          document.getElementById("chango_number_of_paragraphs").style.display = "block";
        } else {
          document.getElementById("chango_number_of_paragraphs").style.display = "none";
        }
      }
      //]]>
      </script>
    <?php
  }
    
  // PHP functions
  function output_option_tag($value, $option, $text) {
    $selected = get_option($option) == $value ? "selected=\"yes\">" : ">";
    $option   = "<option value=\"$value\" ";
    $option   .= $selected;
    $option   .= "$text</option>";
    
    return $option;
  }
?>