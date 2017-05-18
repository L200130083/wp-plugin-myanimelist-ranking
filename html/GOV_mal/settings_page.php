<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>


<div class="wrap">
  <h2>GOV MYANIMELIST</h2>
  <form method="post" action="options.php">
    <?php settings_fields( 'gov_mal_options_group' ); ?>

    <table class="form-table" style="width:100%">
      <tr valign="top">
        <th scope="row"><label for="gov_mal_template">Template</label></th>
        <td>
          <textarea 
            style="width:100%" 
            type="text" 
            id="gov_mal_template" 
            name="gov_mal_template"><?php echo trim(get_option('gov_mal_template')); ?></textarea> 
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="gov_mal_exclude">Don't Run If Post Contains</label></th>
        <td>
          <textarea 
            style="width:100%" 
            type="text" 
            id="gov_mal_exclude" 
            name="gov_mal_exclude"><?php echo trim(get_option('gov_mal_exclude')); ?></textarea> 
        </td>
      </tr>
      <tr valign="top">
        <th scope="row"><label for="gov_mal_max_age">Cache Max Age (day(s))</label></th>
        <td>
          <input 
            style="width:100%" 
            type="number" 
            id="gov_mal_max_age" 
            name="gov_mal_max_age"
            value="<?php echo trim(get_option('gov_mal_max_age')); ?>" />
        </td>
      </tr>
    </table>
    <?php do_settings_sections('gov_mal_options_group'); ?>
    <?php  submit_button(); ?>
  </form>
</div>
