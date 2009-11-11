<?php

/**
 * ER Developer Toolbar
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERDeveloperToolbar
 * @version 1.0.0
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er_developer_toolbar/
 */


if ( ! defined('EXT')) exit('Invalid file request');


class Er_developer_toolbar
{
   
   var $settings = array();

   var $name = 'ER Developer Toolbar';
   var $version = '1.0.0';
   var $description = 'Adds a developer toolbar as a global variable available within your templates';
   var $settings_exist = 'y';
   var $docs_url = '';


   /**
   * PHP4 Constructor
   *
   * @see __construct()
   */

   function Er_developer_toolbar($settings='')
   {
      $this->__construct($settings);
   }

   
   /**
   * PHP 5 Constructor
   *
   * @param array|string  Extension settings associative array or an empty string
   */
   function __construct($settings='')
   {
      global $IN, $SESS;
      
      if(isset($SESS->cache['er']) === FALSE){ $SESS->cache['er'] = array();}

		$this->settings = $this->_get_settings();
   }


   /**
   * Configuration for the extension settings page
   *
   * @return array
   */
   function _get_settings($force_refresh = FALSE, $return_all = FALSE)
	{

		global $SESS, $DB, $REGX, $LANG, $PREFS;

		// assume there are no settings
		$settings = FALSE;
		
		// Get the settings for the extension
		if(isset($SESS->cache['er']['Er_developer_toolbar']['settings']) === FALSE || $force_refresh === TRUE)
		{
			// check the db for extension settings
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = 'Er_developer_toolbar' LIMIT 1");

			// if there is a row and the row has settings
			if ($query->num_rows > 0 && $query->row['settings'] != '')
			{
				// save them to the cache
				$SESS->cache['er']['Er_developer_toolbar']['settings'] = $REGX->array_stripslashes(unserialize($query->row['settings']));
			}
		}
		
		// check to see if the session has been set
		// if it has, return the session
		// if not, return false
		if(empty($SESS->cache['er']['Er_developer_toolbar']['settings']) !== TRUE)
		{
			$settings = ($return_all === TRUE) ?  $SESS->cache['er']['Er_developer_toolbar']['settings'] : $SESS->cache['er']['Er_developer_toolbar']['settings'][$PREFS->ini('site_id')];
		}

		return $settings;
	}
   
   
   /**
    * Customize the settings form display
    *
    * @param      $current is the current settings array
    * @return     string
    */
   function settings_form($current)
   {
      global $DB, $DSP, $LANG, $IN, $PREFS, $SESS;

      // Local storage for our settings
		$s = $SESS->cache['er']['Er_developer_toolbar']['settings'][$PREFS->ini('site_id')];
      $s['check_for_extension_updates'] = (array_key_exists('check_for_extension_updates',$s)) ? $s['check_for_extension_updates'] : '' ;
      // Get my gravatar
      $grav_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5(strtolower('erik@erikreagan.com'))."&amp;default=".urlencode('http://erikreagan.com/gravatar.jpg')."&amp;size=70";
      
      // Grab the member groups from our current site
      $member_groups = $DB->query("SELECT group_id,site_id,group_title FROM exp_member_groups WHERE `site_id` = " . $PREFS->ini("site_id"));

      // Create an array of our member groups in the format that $settings needs
      foreach ($member_groups->result as $group)
      {
         $member_groups_array[$group['group_id']] = $group['group_title'];
      }

      
      // It just looks better...
      $DSP->crumbline = TRUE;
      
      
      // a little BK flavor
      $lgau_query = $DB->query("SELECT class FROM exp_extensions WHERE class = 'Lg_addon_updater_ext' AND enabled = 'y' LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;
		$check_for_extension_updates = ($lgau_enabled AND $s['check_for_extension_updates'] == 'y') ? TRUE : FALSE;
      
      
      // Start the body content string
      $b = '
      <script type="text/javascript" charset="utf-8">
       function toggleToolbar()
       {
          var toolbar = $("#er_developer_toolbar");
          var offset = toolbar.css("top");
          if (offset == "-40px")
          {
             $("#er_developer_toolbar").css({"background":$("#background_color").attr("value"),"border-color":$("#border_color").attr("value")});
             $("#er_developer_toolbar p").css({"color":$("#font_color").attr("value")});
             $("#er_developer_toolbar .link").css({"color":$("#link_color").attr("value")});
             $("#er_developer_toolbar > .horizontal_logo").css({"background-image":"url("+$("#horizontal_logo").attr("value")+")"});
             $("#er_developer_toolbar > .vertical_logo").css({"background-image":"url("+$("#vertical_logo").attr("value")+")"});

             $("#preview_toolbar").text("Hide Custom Branded Toolbar");
             toolbar.animate({top:"0px"});
          }
          else
          {
             $("#preview_toolbar").text("Preview Custom Branded Toolbar");
             toolbar.animate({top: "-40px"});
          }
       }
       function updateValue(element,property,input)
       {
          var toolbar = "#er_developer_toolbar";
          if ($(input).attr("value") == $(toolbar+element).css(property) ) return
          $(toolbar+" "+element).css(property,$(input).attr("value"));
          if ($(toolbar).css("top") == "-40px")
          {
             $("#preview_toolbar").text("Hide Custom Branded Toolbar");
             $(toolbar).animate({top:"0px"});
          }
       }
       function updateBackground(element,input)
       {
          var toolbar = "#er_developer_toolbar";
          if ($(input).attr("value") == $(toolbar+element).css("background-image") ) return
          $(toolbar+" "+element).css("background-image","url("+$(input).attr("value")+")");
          if ($(toolbar).css("top") == "-40px")
          {
             $("#preview_toolbar").text("Hide Custom Branded Toolbar");
             $(toolbar).animate({top:"0px"});
          }
       }
       function changeTooltip(input)
       {
          $(".vertical_logo").attr({title: input.value});
          $(".horizontal_logo").attr({title: input.value});
       }
      </script>
      <style type="text/css">
      .abox { width: 48%; float: left; }
      .abox.left { margin-right: 4%; }
      .right { float: right; }
      .centered { margin: 0 auto; text-align: center; }
      .submit {
         background: #6D942C;
         color: #fff;
         border: 1px solid #fff;
         padding: 4px 15px;
         font-size: 12pt;
         display: block;
         margin: 0 auto;
         -moz-border-radius: 9px;
         -webkit-border-radius: 9px;
      }
      #er_developer_toolbar {
         position: fixed;
         top: -40px;
         width: 90%;
         left: 4%;
         padding: 5px 1% 5px;
         background: #d8dbe5;
         color: #000;
         border: 1px solid #434343;
         border-top: none;
         -moz-border-radius-bottomleft: 5px;
         -moz-border-radius-bottomright: 5px;
         -webkit-border-bottom-left-radius: 5px;
         -webkit-border-bottom-right-radius: 5px;
      }
      #er_developer_toolbar .horizontal_logo {
         position: absolute;
         top: 4px;
         right: 15px;
         background-position: 0 0;
         background-repeat: no-repeat;
         width: 100px;
         height: 25px;
      }
      #er_developer_toolbar .vertical_logo {
         position: absolute;
         top: 5px;
         right: 130px;
         background-position: 0 0;
         background-repeat: no-repeat;
         width: 25px;
         height: 25px;
      }
      a:active { outline: none; }
      a:focus { -moz-outline-style: none; }
      #er_developer_toolbar p, #er_developer_toolbar strong { font-size: 10pt; }
      #er_developer_toolbar .link { color: #0f2f5b; cursor: pointer; }
      #er_developer_toolbar .link:hover { text-decoration: underline; }
      </style>
';
      
      // Preview toolbar
      $b .= '
		<div id="er_developer_toolbar">
		   <p><strong>Color Previews</strong>: This is the standard text color&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;<span class="link">This is the linked text color</span></p>
		   <div class="horizontal_logo" title="ER Developer Toolbar, by Erik Reagan"></div>
		   <div class="vertical_logo" title="ER Developer Toolbar, by Erik Reagan"></div>
		</div>
		
';
		
		$b .= $DSP->div('box')
		   . '<div style="width:auto;overflow:auto;">
               <img src="'.$grav_url.'" alt="Erik Reagan" height="70" width="70" style="border: 1px solid #555;padding: 1px;float:right;"/>'
               . $DSP->heading($LANG->line('toolbar_name') . " &nbsp;&nbsp;<small>{$this->version}</small>").'<br/>'
               . '<p>by '.$DSP->anchor('http://erikreagan.com','Erik Reagan').' of '.$DSP->anchor('http://idealdesignfirm.com','Ideal Design Firm, LLC').'<br/>
               Contact me at '. $DSP->mailto('erik@erikreagan.com','erik@erikreagan.com').'</p>'
         . '</div>'
		   . $DSP->div_c();
		      
      // Start the settings form
      $b .= $DSP->form_open(
               array(
                     'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings'
                  ),
               array(
                     'name' => strtolower('Er_developer_toolbar')
                  )
            );
            
      // container for left and right panes
      $b .= '<div style="overflow:auto;margin-bottom:20px">';
      
      // Create the left pane
      $b .= $DSP->div('abox left','left');
      $b .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'width: 100%'));
      $b .= $DSP->tr()
			. $DSP->td('tableHeading', '', '2')
         . $LANG->line('general_settings')
			. $DSP->td_c()
			. $DSP->tr()
			. $DSP->td('','','2')
			. "<div style='background:#FCFCE1;border-width:0 0 2px 0; margin:0; padding:8px 5px 7px 5px'><p style='font-size:12px;'>".$LANG->line('settings_instructions')."</p></div>"
			. $DSP->td_c()
			. $DSP->tr_c()
			. $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('groups')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_select_header('groups[]',1,4,'250px');
      foreach ($member_groups_array as $group_id => $group_title)
      {
         $selected = (in_array($group_id,$s['groups'])) ? 1 : 0 ;
         $b .= $DSP->input_select_option($group_id, $group_title,$selected);
      }
      $b .= $DSP->input_select_footer()
         . $DSP->td_c();
   
      $b .= $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('position')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')   
         . $DSP->input_select_header('position','','','250px');
         
      $position_optoins = array(
            'top hor'    => "Top",
            'right vert' => "Right",
            'bot hor'    => "Bottom",
            'left vert'  =>"Left"
         );
      
   foreach ($position_optoins as $css_class => $option_text)
   {
      $selected = ($s['position'] == $css_class) ? 1 : 0 ;
      $b .= $DSP->input_select_option($css_class, $option_text,$selected);
   }
   
      $b .= $DSP->input_select_footer()
         . $DSP->td_c()
         . $DSP->tr_c();   


      $b .= $DSP->tr()
         . $DSP->td('tableCellTwo', '', '1')
         . $LANG->line('new_window')
         . $DSP->td_c()
         . $DSP->td('tableCellTwo', '', '1')
         . '<select name="new_window">'
         . $DSP->input_select_option('y', $LANG->line('yes'), ($s['new_window'] == 'y' ? 'y' : ''))
         . $DSP->input_select_option('n', $LANG->line('no'),  ($s['new_window'] != 'y' ? 'y' : ''))
         . $DSP->input_select_footer()
         . $DSP->td_c()
         . $DSP->tr_c();


		$b .= $DSP->tr()
         . $DSP->td('tableCellTwo', '', '1')
         . $LANG->line('check_for_extension_updates')
         . $DSP->td_c()
         . $DSP->td('tableCellTwo', '', '1')
         . '<select name="check_for_extension_updates"'.($lgau_enabled ? '' : ' disabled="disabled"').'>'
         . $DSP->input_select_option('y', $LANG->line('yes'), ($s['check_for_extension_updates'] == 'y' ? 'y' : ''))
         . $DSP->input_select_option('n', $LANG->line('no'),  ($s['check_for_extension_updates'] != 'y' ? 'y' : ''))
         . $DSP->input_select_footer()
         . ($lgau_enabled ? '' : $LANG->line('lgau_required_message'))
         . $DSP->td_c()
         . $DSP->tr_c();
		

      $b .= $DSP->table_close();
      $b .= $DSP->div_c();
      
      
      // Create the right pane
      $b .= $DSP->div('abox','right');
      $b .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'width: 100%'));
      $b .= $DSP->tr()
			. $DSP->td('tableHeading', '', '2')
         . $LANG->line('branding_settings')
			. $DSP->td_c()
			. $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('horizontal_logo')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_text('horizontal_logo',$s['horizontal_logo'],'','','right','250px','onblur=updateBackground(\'.horizontal_logo\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellTwo', '', '1')
         . $LANG->line('vertical_logo')
         . $DSP->td_c()
         . $DSP->td('tableCellTwo', '', '1')
         . $DSP->input_text('vertical_logo',$s['vertical_logo'],'','','right','250px','onblur=updateBackground(\'.vertical_logo\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('tooltip_text')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_text('tooltip_text',$s['tooltip_text'],'','','right','250px','onblur=changeTooltip(this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellTwo', '', '1')
         . $LANG->line('background_color')
         . $DSP->td_c()
         . $DSP->td('tableCellTwo', '', '1')
         . $DSP->input_text('background_color',$s['background_color'],'','16','right','250px','onblur=updateValue("",\'background-color\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('border_color')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_text('border_color',$s['border_color'],'','16','right','250px','onblur=updateValue("",\'border-color\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellTwo', '', '1')
         . $LANG->line('font_color')
         . $DSP->td_c()
         . $DSP->td('tableCellTwo', '', '1')
         . $DSP->input_text('font_color',$s['font_color'],'','16','right','250px','onblur=updateValue("p",\'color\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()
         
         . $DSP->tr()
         . $DSP->td('tableCellOne', '', '1')
         . $LANG->line('link_color')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_text('link_color',$s['link_color'],'','16','right','250px','onblur=updateValue(".link",\'color\',this)',FALSE)
         . $DSP->td_c()
         . $DSP->tr_c()

         ;
         
      $b .= $DSP->table_close()
      . $DSP->div('box','center')
      . $DSP->anchor('#','Preview Custom Branded Toolbar','onclick="toggleToolbar();return false;" id="preview_toolbar"','')
      . $DSP->div_c();
      $b .= $DSP->div_c();
      
      // Close container div
      $b .= '</div>';
      
      $b .= $DSP->input_submit('Save Settings','submit');
      
      $b .= $DSP->form_close();
      
      $b .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'margin-top:20px;width: 100%'));
      $b .= $DSP->tr()
         . $DSP->td('tableHeading','2')
         . $LANG->line('extension_credits')
         . $DSP->td_c()
         . $DSP->tr_c()
         . $DSP->tr()
         . $DSP->td('tableHeadingAlt','2')
         . $LANG->line('icons')
         . $DSP->td_c()
         . $DSP->tr_c()
         . $DSP->tr()
         . $DSP->td('tableCellTwo','2')
         . '<img src="'.str_replace($_SERVER['DOCUMENT_ROOT'],'',PATH_THEMES).'toolbar/creative-commons.png" style="float:left;margin:6px 14px 0 5px" />'
         . $LANG->line('icon_credits')
         . $DSP->td_c()
         . $DSP->tr_c()
         . $DSP->table_c();
      
      $DSP->set_return_data($LANG->line('toolbar_name').' | '.$LANG->line('extension_settings'),$b,$LANG->line('toolbar_name'));
      $DSP->right_crumb('Documentation','http://erikreagan.com/');
   
   }
   
   
   
   /**
	 * Save Settings
	 * 
	 */
	function save_settings()
	{
		// make somethings global
		global $DB, $IN, $PREFS, $REGX, $SESS;

		$default_settings = array();

		// merge the defaults with our $_POST vars
		$_POST = array_merge($default_settings, $_POST);

		// unset the name
		unset($_POST['name']);
		
		// load the settings from cache or DB
		// force a refresh and return the full site settings
		$settings = $this->_get_settings(TRUE, TRUE);

		// add the posted values to the settings
		$settings[$PREFS->ini('site_id')] = $REGX->xss_clean($_POST);

		// update the settings
		$query = $DB->query($sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($settings)) . "' WHERE class = 'Er_developer_toolbar'");
	}
	
	
	
   /**
   * Activates the extension
   *
   * @return      bool
   */
   function activate_extension()
   {
      global $DB, $PREFS;
      
      // Do we create a template group? If so let's to it here...
      
      
      // By default we want to restrict the settings to the Super Admin group which is group_id '1'
      $default_settings = array(
            'groups'                      => array('1'),
            'new_window'                  => 0,
            'position'                    => 'top hor',
            'tooltip_text'                => '',
            'check_for_extension_updates' => 1,
            'horizontal_logo'             => '',
            'vertical_logo'               => '',
            'background_color'            => '',
            'border_color'                => '',
            'font_color'                  => '',
            'link_color'                  => ''
         );
      
      
      // MSM: Site speficic settings thanks to LG
      // get the list of installed sites
		$query = $DB->query("SELECT * FROM exp_sites");

		// if there are sites - we know there will be at least one but do it anyway
		if ($query->num_rows > 0)
		{
			// for each of the sites
			foreach($query->result as $row)
			{
				// build a multi dimensional array for the settings
				$settings[$row['site_id']] = $default_settings;
			}
		}
      
      $hooks = array(
         'sessions_end'                    => 'sessions_end',
         'lg_addon_update_register_source' => 'lg_addon_update_register_source',
         'lg_addon_update_register_addon'  => 'lg_addon_update_register_addon'
      );

      foreach ($hooks as $hook => $method)
      {
         $sql[] = $DB->insert_string('exp_extensions',
            array(
               'extension_id' => '',
               'class'        => get_class($this),
               'method'       => $method,
               'hook'         => $hook,
               'settings'     => serialize($settings),
               'priority'     => 10,
               'version'      => $this->version,
               'enabled'      => "y"
            )
         );
      }

      // run all sql queries
      foreach ($sql as $query)
      {
         $DB->query($query);
      }
      
      return TRUE;
   }
   
   
   
   /**
    * Update the extension
    *
    * @param string
    * @return bool
    **/
   function update_extension($current='')
   {
       global $DB;

       if ($current == '' OR $current == $this->version)
       {
           return FALSE;
       }

       $DB->query("UPDATE exp_extensions 
                   SET version = '".$DB->escape_str($this->version)."' 
                   WHERE class = 'Er_developer_toolbar'");
   }
   
   
   
   /**
   * Disables the extension the extension and deletes settings from DB
   */
   function disable_extension()
   {
       global $DB, $SESS;
       unset($SESS->cache['er']);
       $DB->query("DELETE FROM exp_extensions WHERE class = 'Er_developer_toolbar'");
   }
   
   
   
   /**
    * Sessions End
    *
    * @param object     the session object
    **/
   function sessions_end( $s )
   {
      global $EXT, $IN, $PREFS;
      
      $theme_path = (substr($PREFS->core_ini['theme_folder_url'], strlen($PREFS->core_ini['theme_folder_url']) - 1) != '/') ? $PREFS->core_ini['theme_folder_url'].'/' : $PREFS->core_ini['theme_folder_url'] ;

      if ( ! in_array($s->userdata['group_id'], $this->settings['groups']) )
      {
         $IN->global_vars['er_developer_toolbar']      = '';
         $IN->global_vars['er_developer_toolbar_head'] = '';
         
         return;
      }

      
      $user_access = array(
         'can_access_cp' => $s->userdata['can_access_cp'],
         'can_access_admin' => $s->userdata['can_access_admin'],
         'can_access_design' => $s->userdata['can_access_design'],
         'can_access_modules' => $s->userdata['can_access_modules'],
         'can_access_edit' => $s->userdata['can_access_edit'],
         'can_admin_utilities' => $s->userdata['can_admin_utilities'],
         'can_admin_preferences' => $s->userdata['can_admin_preferences'],
         'can_admin_members' => $s->userdata['can_admin_members']
         );      
      
      if ($EXT->last_call !== FALSE)
		{
			$s =& $EXT->last_call;
		}
      
      $IN->global_vars['er_developer_toolbar_head'] = "
   <link rel='stylesheet' href='".$theme_path."toolbar/style.css' type='text/css' title='no title' charset='utf-8' />";      
      
      if (($this->settings['background_color'] != '') || ($this->settings['border_color'] != '') || ($this->settings['font_color'] != '') || ($this->settings['link_color'] != '') || ($this->settings['vertical_logo'] != '') || ($this->settings['horizontal_logo'] != ''))
      {
         $IN->global_vars['er_developer_toolbar_head'] .= "
   <style type='text/css'>";
         if ($this->settings['background_color'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar,
      #er_developer_toolbar ul > li:hover div.sub ul
      { background-color: ".$this->settings['background_color']."; }
";
         }
         if ($this->settings['border_color'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar,
      #er_developer_toolbar ul > li:hover div.sub ul
      { border-color: ".$this->settings['border_color']." !important; }
      #er_developer_toolbar.hor .divider {
      width: 1px;height: 18px;margin: 3px 10px;background:none;border-left: 1px solid ".$this->settings['border_color']." }
      #er_developer_toolbar.vert .divider {
      width: 18px;height: 1px;margin: 10px 3px;background:none;border-top: 1px solid ".$this->settings['border_color']." }";
         }
         if (($this->settings['border_color'] != '') || (($this->settings['background_color'] != '')))
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar .arrow { display: none !important; }
";
         }
         if ($this->settings['horizontal_logo'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar.hor p.toolbar_heading { background: url(".$this->settings['horizontal_logo'].") 0 0; }";
         }
         if ($this->settings['vertical_logo'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar.vert p.toolbar_heading { background: url(".$this->settings['vertical_logo'].") 0 0; }";
         }
         if ($this->settings['font_color'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar { color: ".$this->settings['font_color']."; }";
         }
         if ($this->settings['link_color'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      #er_developer_toolbar a,#er_developer_toolbar a:visited { color: ".$this->settings['link_color']."; }";
         }
         $IN->global_vars['er_developer_toolbar_head'] .= "
   </style>";
      }
   
   
      $IN->global_vars['er_developer_toolbar_head'] .= "
   <script src='".$theme_path."toolbar/jqcheck.js' type='text/javascript' charset='utf-8'></script>
   <script src='".$theme_path."toolbar/general.js' type='text/javascript' charset='utf-8'></script>";
      
      if ($this->settings['new_window'] == 'y')
      {
         $IN->global_vars['er_developer_toolbar_head'] .= "
   <script tyle='text/javascript' charset='utf-8'>
      $(document).ready(function(){
         jQuery('#er_developer_toolbar a').attr('target','_blank');
         jQuery('#er_developer_toolbar a.self').attr('target','');
      });
   </script>
";
      }
      

      $IN->global_vars['er_developer_toolbar'] = $this->_create_toolbar($user_access);
      
   }
   
   
     
   /**
    * Create extensions sub-menu
    * 
    * @access Private
    * @return string
    */
   function _create_ext_menu()
   {
      global $DB;
      
      $ext_menu = '';
      
      $enabled_extensions_results = $DB->query("SELECT class,settings,enabled FROM exp_extensions WHERE enabled = 'y' GROUP BY class");

      if ($enabled_extensions_results->num_rows == 0)
      {
         return $ext_menu;
      } else {
         
          foreach($enabled_extensions_results->result as $row)
          {
             $settings_exist = ($row['settings'] == '') ? 'n' : 'y' ;
             $enabled_extensions[] = array('class' => $row['class'], 'settings' => $settings_exist);
          }
      }

      // Start sub-menu
      $ext_menu .= "<div class='sub2'>
         <ul>
            <li><strong>Extension Settings</strong></li>
            ";

      foreach ($enabled_extensions as $ext) {
         if ($ext['settings'] == 'y')
         {
            $name = ucwords(str_replace('_',' ',str_replace('_ext','',$ext['class'])));
            
            $ext_menu .= "<li><a href='".CP_URL."?C=admin&M=utilities&P=extension_settings&name=".strtolower($ext['class'])."'>$name</a></li>\n";
         }
      }

      // Close sub-menu ul and div
      $ext_menu .= "
         </ul>
         <span class='arrow'></span>
      </div>
";

      return $ext_menu;
      
   }
   
   
   /**
    * Create extensions sub-menu
    * 
    * @access Private
    * @return string
    */
   function _create_pi_menu()
   {
      global $FNS;
      
      $pi_menu = '';

      $pi_files = $FNS->create_directory_map(PATH_PI, TRUE);
      if (($pi_files == NULL) || (count($pi_files) == 0))
      {
         return $pi_menu;
      }
      
      // Start sub-menu
      $pi_menu .= "<div class='sub2'>
         <ul>
            <li><strong>Plugin Usage</strong></li>
            ";

      foreach ($pi_files as $pi) {

         if (strpos($pi,"pi.") !== FALSE)
         {
            $name = ucwords(str_replace('_',' ',preg_replace('/pi\.|\.php/','',$pi)));
            $pi_menu .= "<li><a href='".CP_URL."?C=admin&amp;M=utilities&amp;P=plugin_info&amp;name=".preg_replace('/pi\.|\.php/','',$pi)."'>$name</a></li>\n";
         }
      }
      
      // Close sub-menu ul and div
      $pi_menu .= "
         </ul>
         <span class='arrow'></span>
      </div>
";

      return $pi_menu;
      
   }
   
   
   
   
   
   /**
    * Create Developer toolbar
    * 
    * @access Private
    * @return string
    */
   function _create_toolbar($user_access)
   {
      global $DB, $DSP, $PREFS, $SESS;
      
      // Get our settings from the cache
      $s = $SESS->cache['er']['Er_developer_toolbar']['settings'][$PREFS->ini('site_id')];
      define('CP_URL',$PREFS->core_ini['cp_url']);
      
      $toolbar = '';
      
      // Get settings for site and debug and set CSS classes
      $system_status = ($PREFS->core_ini['is_system_on'] == 'y') ? 'on' : 'off' ;
      $template_debugging = ($PREFS->core_ini['template_debugging'] == 'y') ? 'on' : 'off' ;
      $show_queries = ($PREFS->core_ini['show_queries'] == 'y') ? 'on' : 'off' ;
      $tooltip_text = ($s['tooltip_text'] == '') ? 'ER Developer Toolbar: by Erik Reagan' : $s['tooltip_text'] ;
   
      
      // hard coded some language lines because I couldn't get $LANG working...
      // will check back to fix later
      switch ($PREFS->core_ini['debug'])
      {
         case '1':
            $debug_status = 'on';
            $debug_message = 'PHP/SQL error messages shown only to Super Admins';
            break;
            
         case '2':
            $debug_status = 'on2';
            $debug_message = 'PHP/SQL error messages shown to anyone - NOT SECURE';
            break;
            
         default:
            $debug_status = 'off';
            $debug_message = 'No PHP/SQL error messages generated';
            break;
      }
      
      // Build the toollbar
      $toolbar .= "
<div id='er_developer_toolbar' class='".$this->settings['position']."'>
";

//       // Not quite ready for prime time...
//       $toolbar .= "
//    <div class='icon' id='move'></div>
// ";

      $toolbar .= "
      
   <p title='".$tooltip_text."' class='toolbar_heading'>Developer Toolbar</p>

   <div class='divider'></div>      

   <ul>";
   
      if ($user_access['can_access_cp'] == 'y')
      {
         $toolbar .= "
      <li>
         <a class='icon no_link' id='home' href='#'>CP Home</a>
         <div class='sub'>
            <ul>
               <li><strong>Home Page</strong></li>
               <li><a class='self' href='{site_url}'>{site_name} Home Page</a></li>
               <li><a href='".CP_URL."'>Control Panel Home Page</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon no_link' id='account' href='#'>Member Accounts</a>
         <div class='sub'>
            <ul>
               <li><strong>Member Accounts</strong></li>
               <li><a href='".CP_URL."?C=myaccount'>My Account</a></li>";

      if ($user_access['can_admin_members'] == 'y')
      {
         $toolbar .="
               <li><a href='".CP_URL."?C=admin&amp;M=members&amp;P=mbr_group_manager'>Member Groups</a></li>
               <li><a href='".CP_URL."?C=admin&amp;M=members&amp;P=view_members'>Member List</a></li>";
      }
      
      
      $toolbar .= "
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      $toolbar .= "
      <li>
         <a class='icon no_link' id='logout' href='#'>Logout</a>
         <div class='sub'>
            <ul>
               <li><strong>Logout</strong></li>
               <li><a href='".CP_URL."?C=logout'>Logout as {username}</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
   </ul>

";
      if (($user_access['can_access_cp'] == 'y') && (($user_access['can_access_admin'] == 'y') || ($user_access['can_access_design'] == 'y')))
      {
         $toolbar .= "
   <div class='divider'></div>


   <ul>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y') && ($user_access['can_admin_preferences'] == 'y'))
      {
         $toolbar .= "
      <li>
         <a class='icon no_link' id='statuses' href='#'>Statuses</a>
         <div class='sub'>
            <ul>
               <li><strong>General Statuses</strong></li>
               <li class='status_$system_status'><a title='System is ".ucfirst($system_status)."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=general_cfg' id='system_status'>System Status</a></li>";
      
         if ($PREFS->core_ini['multiple_sites_enabled'] == 'y')
         {
            $site_status = ($PREFS->core_ini['is_site_on'] == 'y') ? 'on' : 'off' ;
            $toolbar .= "
                  <li class='status_$site_status'><a title='Site is ".ucfirst($site_status)."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=general_cfg' id='site_status'>Site Status</a></li>";
         }
      
         $toolbar .= "
               <li class='status_$debug_status'><a title='".$debug_message."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg' id='debug_status'>Debug Status</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_design'] == 'y'))
      {
         $toolbar .= "
      <li>
         <a class='icon no_link' id='templates' href='#'>Template Options</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Manager</strong></li>
               <li><a href='".CP_URL."?C=templates'>Manage Templates</a></li>
               <li><a href='".CP_URL."?C=templates&amp;M=template_prefs_manager'>Manage Preferences</a></li>
               <li><a href='".CP_URL."?C=templates&amp;M=global_variables'>Global Variables</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y') && ($user_access['can_admin_utilities'] == 'y'))
      {
         $toolbar .= "
      <li>
         <a class='icon no_link' id='cache' href='#'>Clear Cache</a>
         <div class='sub'>
            <ul>
               <li><strong>Clear Cache</strong></li>
               <li><a href='".CP_URL."?C=admin&amp;M=utilities&amp;P=clear_cache_form'>Clear all Cache</a></li>";
               
         // Not quite ready for prime time            
         // $toolbar .= "
         //       <li><a href='#'>Page Cache</a></li>
         //       <li><a href='#'>Tag Cache</a></li>
         //       <li><a href='#'>Database Cache</a></li>
         //       <li><a href='#'>SQL Cache</a></li>
         //       <li><a href='#'>Relationships Cache</a></li>
         //       <li><a href='#'>All Cache</a></li>";
               
      $toolbar .= "
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y') || ($user_access['can_access_design'] == 'y'))
      {
         $toolbar .= "
   </ul>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && (($user_access['can_access_admin'] == 'y') || ($user_access['can_access_modules'] == 'y')))
      {
         $toolbar .= "
         
         
   <div class='divider'></div>


   <ul>
      <li>
         <a class='icon no_link' id='addons' href='#'>Addons</a>
         <div class='sub visible'>
            <ul>
               <li><strong>Add-ons</strong></li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y'))
      {
         $toolbar .= "
               <li>
                  <a id='extensions' href='".CP_URL."?C=admin&M=utilities&P=extensions_manager'>Extensions</a>
";
         $toolbar .= $this->_create_ext_menu();

         $toolbar .="
               </li>
               <li>
                  <a id='plugins' href='".CP_URL."?C=admin&amp;M=utilities&amp;P=plugin_manager'>Plugins</a>
";
         $toolbar .= $this->_create_pi_menu();
         
         $toolbar .= "
               </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_modules'] == 'y'))
      {
         $toolbar .= "
               <li><a id='modules' href='".CP_URL."?C=modules'>Modules</a></li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && (($user_access['can_access_admin'] == 'y') || ($user_access['can_access_modules'] == 'y')))
      {
         $toolbar .= "
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y'))
      {
         $toolbar .= "
      <li>
         <a class='icon no_link' id='temp_debug' href='#'>Template Debugging</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Debugging</strong></li>
               <li class='status_".$template_debugging."'><a title='Click to toggle' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg'>Currently ".ucfirst($template_debugging)."</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon no_link' id='sql' href='#'>Display SQL Queries</a>
         <div class='sub'>
            <ul>
               <li><strong>Display SQL Queries</strong></li>
               <li class='status_".$show_queries."'><a title='Click to toggle' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg'>Currently ".ucfirst($show_queries)."</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      }
      
      if (($user_access['can_access_cp'] == 'y') && ($user_access['can_access_admin'] == 'y') || ($user_access['can_access_modules']) == 'y')
      {
         $toolbar .= "
   </ul>";
      }

      $toolbar .= "
   <ul id='clock'>
      <li>
         <a class='icon no_link' id='utility' href='#'>Good to Know</a>
         <div class='sub'>
            <ul>
               <li><strong>Good to Know</strong></li>
               <li title='Elapsed Time' id='toolbar_elapsed_time'>{elapsed_time} seconds</li>
               <li title='Total Queries' id='toolbar_queries'>{total_queries} queries executed</li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
   </ul>

</div>\n\n";

      return $toolbar;
      
   }
   
   
   
   
   /**
    * Register a new Addon Source
    *
    * @param   array $sources The existing sources
    * @return  array The new source list
    * @since   version 1.0.0
    */
   function lg_addon_update_register_source($sources)
   {
       global $EXT;
       // -- Check if we're not the only one using this hook
       if($EXT->last_call !== FALSE)
           $sources = $EXT->last_call;

       // add a new source
       // must be in the following format:
       /*
       <versions>
           <addon id='LG Addon Updater' version='2.0.0' last_updated="1218852797" docs_url="http://leevigraham.com/" />
       </versions>
       */
       if($this->settings['check_for_extension_updates'] == 'yes')
       {
           $sources[] = 'http://erikreagan.com/ee-addons/versions.xml';
       }
       return $sources;

   }


   /**
    * Register a new Addon
    *
    * @param    array $addons The existing sources
    * @return   array The new addon list
    * @since    version 1.0.0
    */
   function lg_addon_update_register_addon($addons)
   {
   	global $EXT;
   	// -- Check if we're not the only one using this hook
   	if($EXT->last_call !== FALSE)
   		$addons = $EXT->last_call;

   	// add a new addon
   	// the key must match the id attribute in the source xml
   	// the value must be the addons current version
   	if($this->settings['check_for_extension_updates'] == 'yes')
   	{
   		$addons['ER Developer Toolbar'] = $this->version;
   	}
   	return $addons;
   }
   
}
// END class 

/* End of file ext.er_developer_toolbar.php */
/* Location: ./system/extensions/ext.er_developer_toolbar.php */