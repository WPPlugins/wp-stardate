<?php

// Security recommendation from wp.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// create custom plugin settings menu
add_action('admin_menu', 'stardate_create_menu');

//call custom post function to associate all posts with stardate
add_action('admin_post_update_stardates', 'stardate_admin_stardate_all_posts');

function stardate_create_menu()
{
    //create new options menu
    add_options_page('Stardate Setup','Stardate','manage_options','stardate','stardate_settings_page');

    //call register settings function
    add_action( 'admin_init', 'register_stardate_settings' );
}

function register_stardate_settings()
{
    //register our settings
    register_setting( 'stardate-settings', 'stardate_prefix', 'stardate_validate_string' );
    register_setting( 'stardate-settings', 'stardate_style', 'stardate_validate_style' );
    register_setting( 'stardate-settings', 'stardate_override_get_date', 'stardate_validate_checkbox' );
    register_setting( 'stardate-settings', 'stardate_override_time', 'stardate_validate_checkbox' );
    register_setting('stardate-settings', 'stardate_override_even_when_explicit_format', 'stardate_validate_checkbox');
}

function stardate_validate_string( $input )
{
    /**
     * validate and escape the string before saving
     * 
     * @var string $input the input to validate
     */
    // TODO: some extra validation maybe. string len, etc    
    return wp_filter_nohtml_kses( $input );
}

function stardate_validate_checkbox( $input )
{
    /**
     * validate the checkboxes
     *
     * @var int $input the input to validate
     */
     return ( $input == 1 ? 1 : 0 );
}

function stardate_validate_style( $input )
{
    /**
     * validate the style. it can be either XI or Classic
     *
     * @var string $input the input to validate
     */
    return ( in_array($input, array('XI', 'XI_wikipedia', 'SOL')) ? $input : 'Classic');
}

function stardate_admin_stardate_all_posts()
{
    /**
     * Stardate all posts. 
     * 
     */

    status_header(200);
    if ( !current_user_can( 'manage_options' ) )
    {
        wp_die( 'unauthorized' );
    }
    
    // here we loop through all posts and set the stardate for them
    $r = stardate_all_posts();

    wp_redirect(  admin_url( 'options-general.php?page=stardate' ) );

}


function stardate_settings_page()
{
?>
<div class="wrap">
  <h2>Stardate Options</h2>
  <form method="post" action="options.php">
    <?php settings_fields( 'stardate-settings' ); ?>
    <?php do_settings_sections( 'stardate-settings' ); ?>
     <table class="form-table">
        <tr valign="top">
          <th scope="row">Stardate Prefix</th>
          <td><input type="text" name="stardate_prefix" value="<?php echo esc_attr( get_option('stardate_prefix') ); ?>" /></td>
        </tr>
        <tr valign="top">
          <th scope="row">Style</th>
          <?php  $s = get_option('stardate_style') ?>
          <td>
            <input name="stardate_style" type="radio" value="Classic" <?php checked( 'Classic', get_option( 'stardate_style' ) ); ?> /> Classic (trekguide) <code><?php echo stardate_now('Classic')?></code>
            <input name="stardate_style" type="radio" value="XI" <?php checked( 'XI', get_option( 'stardate_style' ) ); ?> /> XI (trekguide) <code><?php echo stardate_now('XI')?></code>
            <input name="stardate_style" type="radio" value="XI_wikipedia" <?php checked( 'XI_wikipedia', get_option( 'stardate_style' ) ); ?> /> XI (wikipedia) <code><?php echo stardate_now('XI_wikipedia')?></code>
            <input name="stardate_style" type="radio" value="SOL" <?php checked( 'SOL', get_option( 'stardate_style' ) ); ?> /> StarTrek Online (SOL) <code><?php echo stardate_now('SOL')?></code>
          </td>
        </tr>
        <tr>
          <td colspan=2>
             Themes may use different functions to get the date for a given post etc. There are two funcitons used quite often thoguh: <code>the_date</code> (which cannot be filtered) and <code>get_the_date</code>.
              In some themes like the default one, <code>get_the_date</code> is used for the date tag with the 'U' format, and to keep that format, the default behaviour is to NOT override get_the_date if it is called with explicit date formatting string. When overriden, the functions call the  <code> get_the_stardate </code> or <code>the_stardate</code> functions, which can also be used directly in the templates.
          </td>
        </tr>
          <tr valign="top">
          <th scope="row">Override get_the_date</th>
          <td>
            <input name="stardate_override_get_date" type="checkbox" value="1" <?php checked( '1', get_option( 'stardate_override_get_date' ) ); ?> />
          </td>
        </tr>
          <tr valign="top">
            <th scope="row">Override the_time</th>
            <td>
              <input name="stardate_override_time" type="checkbox" value="1" <?php checked( '1', get_option( 'stardate_override_time' ) ); ?> />
            </td>
          </tr>
         <tr valign="top">
             <th scope="row">Override even when a format is passed to the get_the_date functions</th>
             <td>
                 <input name="stardate_override_even_when_explicit_format" type="checkbox" value="1" <?php checked( '1', get_option( 'stardate_override_even_when_explicit_format' ) ); ?> />
             </td>
         </tr>
     </table>
    <?php submit_button( 'Make it so'); ?>
  </form>
  <form method="post" action="admin-post.php">
      <table class="form-table">
          <tr valign="top">
          <th scope="row">Associate stardate with all posts</th>
        </tr>
        <tr>
          <td>
            Press the button down here to calculate stardate for all posts. Needed for having stardate in the url. Needs pushing when style is changed etc.
          </td>
        </tr>
        <tr>
          <td>
             <input type="hidden" name="action" value="update_stardates" />
             <?php submit_button( 'Set stardate for all posts', 'secondary'); ?>
           </td>
         </tr>
       </table>
   </form>
</div>
<?php } ?>
