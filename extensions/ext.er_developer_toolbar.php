<?php
// ini_set('error_reporting',E_ALL);
/**
 * ER Developer Toolbar
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERDeveloperToolbar
 * @version 1.1.2
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er-developer-toolbar/
 * @license http://creativecommons.org/licenses/by-nd/3.0/ Creative Commons Attribution-No Derivative Works 3.0 Unported
 */


if ( ! defined('EXT')) exit('Invalid file request');


define('ER_DTB_name', 'ER Developer Toolbar');
define('ER_DTB_version', '1.1.2');
define('ER_DTB_underscores', 'Er_developer_toolbar');


class Er_developer_toolbar
{
   
   private  $settings       = array();
   public   $name           = ER_DTB_name;
   public   $version        = ER_DTB_version;
   public   $description    = 'Adds a developer toolbar as a global variable available within your templates';
   public   $settings_exist = 'y';
   public   $docs_url       = 'http://erikreagan.com/d/toolbar/';


   /**
   * PHP4 Constructor
   *
   * @access   public
   * @see      __construct()
   */

   public function Er_developer_toolbar($settings='')
   {
      $this->__construct($settings);
   }

   
   /**
   * PHP 5 Constructor
   *
   * @param    array|string  Extension settings associative array or an empty string
   * @access   public
   */
   public function __construct($settings='')
   {
      global $IN, $SESS;
      
      if(isset($SESS->cache['er']) === FALSE) { $SESS->cache['er'] = array(); }

		$this->settings = $this->get_settings();
   }


   /**
   * Configuration for the extension settings page
   *
   * @access   public
   * @return   array
   */
   public function get_settings($force_refresh = FALSE, $return_all = FALSE)
	{

		global $SESS, $DB, $REGX, $LANG, $PREFS;

		// assume there are no settings
		$settings = FALSE;
		
		// Get the settings for the extension
		if(isset($SESS->cache['er'][ER_DTB_underscores]['settings']) === FALSE || $force_refresh === TRUE)
		{
			// check the db for extension settings
			$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '".ER_DTB_underscores."' LIMIT 1");

			// if there is a row and the row has settings
			if ($query->num_rows > 0 && $query->row['settings'] != '')
			{
				// save them to the cache
				$SESS->cache['er'][ER_DTB_underscores]['settings'] = $REGX->array_stripslashes(unserialize($query->row['settings']));
			}
		}
		
		// check to see if the session has been set
		// if it has, return the session
		// if not, return false
		if(empty($SESS->cache['er'][ER_DTB_underscores]['settings']) !== TRUE)
		{
		   $settings = ($return_all === TRUE) ?  $SESS->cache['er'][ER_DTB_underscores]['settings'] : $SESS->cache['er'][ER_DTB_underscores]['settings'][$PREFS->ini('site_id')];
		}

		return $settings;
	}
   
   
   /**
    * Customize the settings form display
    *
    * @param      $current is the current settings array
    * @access     public
    * @return     string
    */
   public function settings_form($current)
   {
      global $DB, $DSP, $LANG, $IN, $PREFS, $SESS;

      // Local storage for our settings
		$s = $SESS->cache['er'][ER_DTB_underscores]['settings'][$PREFS->ini('site_id')];
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
               . $DSP->heading($LANG->line('toolbar_name') . " &nbsp;&nbsp;<small>{".ER_DTB_version."}</small>").'<br/>'
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
                     'name' => strtolower(ER_DTB_underscores)
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
            'top'   => "Top",
            'right' => "Right",
            'bot'   => "Bottom",
            'left'  => "Left"
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
      $DSP->right_crumb('Documentation',$this->docs_url);
   
   }
   
   
   
   /**
	 * Save Settings
	 * 
	 * @access     public
	 */
	public function save_settings()
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
		$settings = $this->get_settings(TRUE, TRUE);

		// add the posted values to the settings
		$settings[$PREFS->ini('site_id')] = $REGX->xss_clean($_POST);

		// update the settings
		$query = $DB->query($sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($settings)) . "' WHERE class = '".ER_DTB_underscores."'");
	}
	
	
	
   /**
   * Activates the extension
   *
   * @access      public
   * @return      bool
   */
   public function activate_extension()
   {
      global $DB, $PREFS;
      
      // Do we create a template group? If so let's to it here...
      
      
      // By default we want to restrict the settings to the Super Admin group which is group_id '1'
      $default_settings = array(
            'groups'                      => array('1'),
            'new_window'                  => 0,
            'position'                    => 'top',
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
               'priority'     => 1,
               'version'      => ER_DTB_version,
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
    * @param      string
    * @access     public
    * @return     bool
    */
   public function update_extension($current='')
   {
       global $DB;
       
       if ($current == '' OR $current == ER_DTB_version)
       {
           return FALSE;
       }
       
       $DB->query("UPDATE exp_extensions 
                   SET version = '".$DB->escape_str(ER_DTB_version)."' 
                   WHERE class = '".ER_DTB_underscores."'");
   }
   
   
   
   /**
    * Disables the extension the extension and deletes settings from DB
    */
   public function disable_extension()
   {
       global $DB, $SESS;
       unset($SESS->cache['er']);
       $DB->query("DELETE FROM exp_extensions WHERE class = '".ER_DTB_underscores."'");
   }
    
    
   
   /**
    * Sessions End
    *
    * @param   object     $s    the session object
    * @access  public
    */
   public function sessions_end( $s )
   {
      global $EXT, $IN, $PREFS;
      
      // If our logged user isn't in a permitted member group then we can just stop here
      if ( ! in_array($s->userdata['group_id'], $this->settings['groups']) )
      {
         $IN->global_vars['er_developer_toolbar']      = '';
         $IN->global_vars['er_developer_toolbar_head'] = '';
         
         return;
      }


      // Get the returned sessions_end data from any other extensions using this hook
      if ($EXT->last_call !== FALSE)
		{
			$s =& $EXT->last_call;
		}


      // Make sure our theme_folder_url has a trailing slash
      $theme_path = (substr($PREFS->ini('theme_folder_url'),-1,1) != '/') ? $PREFS->ini('theme_folder_url').'/' : $PREFS->ini('theme_folder_url') ;


      // Since session data isn't available in our other functions in this class we will pass along this info from our session
      $is_super_admin = ($s->userdata['group_id'] == '1') ? TRUE : FALSE ;
      $user_access = array(
         'is_super_admin'        => $is_super_admin,
         'group_id'              => $s->userdata['group_id'],
         'can_access_cp'         => $s->userdata['can_access_cp'],
         'can_access_admin'      => $s->userdata['can_access_admin'],
         'can_access_design'     => $s->userdata['can_access_design'],
         'can_access_modules'    => $s->userdata['can_access_modules'],
         'can_access_edit'       => $s->userdata['can_access_edit'],
         'can_admin_utilities'   => $s->userdata['can_admin_utilities'],
         'can_admin_preferences' => $s->userdata['can_admin_preferences'],
         'can_admin_templates'   => $s->userdata['can_admin_templates'],
         'can_admin_members'     => $s->userdata['can_admin_members']
         );      

      // start building our global variables
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
      .erdtb_hor #er_developer_toolbar p.toolbar_heading { background: url(".$this->settings['horizontal_logo'].") 0 0; }";
         }
         if ($this->settings['vertical_logo'] != '')
         {
            $IN->global_vars['er_developer_toolbar_head'] .= "
      .erdtb_vert #er_developer_toolbar p.toolbar_heading { background: url(".$this->settings['vertical_logo'].") 0 0; }";
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
   <script type='text/javascript' charset='utf-8'>
      $(document).ready(function(){
         jQuery('#er_developer_toolbar a').attr('target','_blank');
         jQuery('#er_developer_toolbar a.self').attr('target','');
      });
   </script>
";
      }
      
      
      $IN->global_vars['er_developer_toolbar'] = $this->create_toolbar($user_access);
      
   }
   
   
   
   /**
    * Create module sub-menu
    * 
    * @param      user member group ID
    * @access     private
    * @since      1.0.1
    * @return     string
    */
   private function create_mod_menu($user_group_id = NULL)
   {
      global $DB, $SESS;
      $mod_menu = '';
      
      if ($user_group_id == '1')
      {
         $enabled_modules_results = $DB->query("SELECT module_name
                                                FROM exp_modules
                                                WHERE has_cp_backend = 'y'
                                                ORDER BY module_name
                                                ");
      } else {      
         $enabled_modules_results = $DB->query("SELECT exp_modules.module_id AS m_id,module_name
                                                FROM exp_modules
                                                JOIN exp_module_member_groups
                                                ON exp_modules.module_id = exp_module_member_groups.module_id
                                                WHERE has_cp_backend = 'y'
                                                AND exp_module_member_groups.group_id = $user_group_id
                                                ORDER BY module_name
                                                ");
      }

      if ($enabled_modules_results->num_rows == 0)
      {
         return $mod_menu;
      } else {
         
          // Start sub-menu
          $mod_menu .= "<div class='sub2'>
             <ul>
                <li><strong>Module CP Pages</strong></li>
                ";

          foreach ($enabled_modules_results->result as $mod) {
             $name = ucwords(str_replace('_',' ',str_replace('_ext','',$mod['module_name'])));
             $mod_menu .= "<li><a href='".CP_URL."?C=modules&M=".$mod['module_name']."'>$name</a></li>\n";
          }

          // Close sub-menu ul and div
          $mod_menu .= "
             </ul>
             <span class='arrow'></span>
          </div>
";
      }

      return $mod_menu;
  
   }
   
   
   
     
   /**
    * Create extensions sub-menu
    * 
    * @access     private
    * @since      1.0.0
    * @return     string
    */
   private function create_ext_menu()
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
    * Create plugin sub-menu
    * 
    * @access     private
    * @since      1.0.0
    * @return     string
    */
   private function create_pi_menu()
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
    * @param      user access
    * @access     private
    * @return     string
    */
   private function create_toolbar($user_access)
   {
      global $DB, $DSP, $PREFS, $SESS;
      
      // Get our settings from the cache
      $s = $SESS->cache['er'][ER_DTB_underscores]['settings'][$PREFS->ini('site_id')];
      define('CP_URL',$PREFS->core_ini['cp_url']);
      
      $toolbar = '';
      
      switch ($s['position']) {
         case 'top':
            $toolbar_class = 'hor';
            break;
            
         case 'bottom':
            $toolbar_class = 'hor';
            break;
            
         case 'left':
            $toolbar_class = 'vert';
            break;
            
         case 'right':
            $toolbar_class = 'vert';
            break;
   
         default:
            $toolbar_class = 'hor';
            break;
      }
   
      $tooltip_text = ($s['tooltip_text'] == '') ? 'ER Developer Toolbar: by Erik Reagan' : $s['tooltip_text'] ;
   
      
      
      // Build the toollbar
      $toolbar .= "
      <div class='er_developer_toolbar_container erdtb_".$toolbar_class."' id='erdtb_".$s['position']."'>
<div id='er_developer_toolbar'>";

      $toolbar .= "
      
   <p title='".$tooltip_text."' class='toolbar_heading'>Developer Toolbar</p>

   ";
   
      $toolbar .= $this->piece_divider($user_access,'front');
   
   
      
      // This is the magic opener
      $toolbar .= "
   <ul>";
   
   
      $toolbar .= $this->piece_home($user_access);
      $toolbar .= $this->piece_accounts($user_access);
      $toolbar .= $this->piece_logout();
      

      $toolbar .= $this->piece_divider($user_access);

      
      if ($user_access['is_super_admin'] || ($user_access['can_access_cp'] == 'y' && $user_access['can_access_admin'] == 'y' && $user_access['can_admin_preferences'] == 'y'))
      {
         $toolbar .= $this->piece_statuses($user_access);
      }
      if ($user_access['is_super_admin'] || ($user_access['can_access_cp'] == 'y' && $user_access['can_access_design'] == 'y' && $user_access['can_admin_templates'] == 'y'))
      {
         $toolbar .= $this->piece_templates($user_access);
      }
      if ($user_access['is_super_admin'] || $user_access['can_admin_utilities'] == 'y')
      {
         $toolbar .= $this->piece_cache($user_access);
      }
      if ($user_access['is_super_admin'] || ($user_access['can_admin_templates'] == 'y' || $user_access['can_admin_utilities'] == 'y' || ($user_access['can_admin_preferences'] == 'y' && $user_access['can_access_admin'] == 'y')))
      {
         $toolbar .= $this->piece_divider($user_access);
      }
      
      if ($user_access['is_super_admin'] || (($user_access['can_access_admin'] == 'y' && $user_access['can_admin_utilities'] == 'y') || $user_access['can_access_modules'] == 'y'))
      {
         $toolbar .= $this->piece_addons($user_access);
      }
      
      if ($user_access['is_super_admin'] || $user_access['can_admin_preferences'] == 'y')
      {
         $toolbar .= $this->piece_temp_debug();
         $toolbar .= $this->piece_sql_queries();
      }
      
      
      
      // this is the magic closing UL
      $toolbar .= "
   </ul>";
   
      
      $toolbar .= $this->piece_load_stats();
   
   
      // And now the closing div tag for the entire toolbar
      $toolbar .= "
</div>\n\n</div>";

      return $toolbar;
      
   }
   
   
   /**
    * Add move tool
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_move($access = NULL)
   {
      return "
      <ul>
      <li>
         <a class='icon no_link' id='move' href='#'>Move Toolbar</a>
         <div class='sub'>
            <ul>
               <li><strong>Move Toolbar</strong></li>
               <li><a id='move_top' href='#'>Top</a></li>
               <li><a id='move_bot' href='#'>Bottom</a></li>
               <li><a id='move_left' href='#'>Left</a></li>
               <li><a id='move_right' href='#'>Right</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      </ul>";
   }


   
   
   
   
   /**
    * Add home links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_home($access = NULL)
   {
      $home = "
      <li>
         <a class='icon no_link' id='home' href='#'>CP Home</a>
         <div class='sub'>
            <ul>
               <li><strong>Home Page</strong></li>
               <li><a class='self' href='{site_url}'>{site_name} Home Page</a></li>
               ";
      if ($access['is_super_admin'] || $access['can_access_cp'] == 'y')
      {
         $home .= "<li><a href='".CP_URL."'>Control Panel Home Page</a></li>
               ";  
      }
      $home .= "</ul>
            <span class='arrow'></span>
         </div>
      </li>";
      
      return $home;
   }


   
   
   
   
   /**
    * Add account links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_accounts($access = NULL)
   {
      $accounts = "
      <li>
         <a class='icon no_link' id='account' href='#'>Member Accounts</a>
         <div class='sub'>
            <ul>
               <li><strong>Member Accounts</strong></li>
               <li><a href='".CP_URL."?C=myaccount'>My Account</a></li>
               ";
      if ($access['is_super_admin'] || ($access['can_access_cp'] == 'y' && $access['can_admin_members'] == 'y'))
      {
         $accounts .= "
               <li><a href='".CP_URL."?C=admin&amp;M=members&amp;P=mbr_group_manager'>Member Groups</a></li>
               <li><a href='".CP_URL."?C=admin&amp;M=members&amp;P=view_members'>Member List</a></li>
               ";
      }
      $accounts .= "
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      
      return $accounts;
      
   }
   
   
   
   
   
   /**
    * Add logout link
    *
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_logout()
   {
      return "
      <li>
         <a class='icon no_link' id='logout' href='#'>Logout</a>
         <div class='sub'>
            <ul>
               <li><strong>Logout</strong></li>
               <li><a href='".CP_URL."?C=logout'>Logout as {username}</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
   }
   
   
   
   
   
   /**
    * Add status links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_statuses($access = NULL)
   {
      global $PREFS;
      
      $system_status = ($PREFS->core_ini['is_system_on'] == 'y') ? 'on' : 'off' ;
      
      // hard coded some language lines because I couldn't get $LANG working...
      // will check back to fix later
      switch ($PREFS->ini('debug'))
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
      
      $statuses = "
      <li>
         <a class='icon no_link' id='statuses' href='#'>Statuses</a>
         <div class='sub'>
            <ul>
               <li><strong>General Statuses</strong></li>
               <li class='status_$system_status'><a title='System is ".ucfirst($system_status)."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=general_cfg' id='system_status'>System Status</a></li>";
      
      if ($PREFS->ini('multiple_sites_enabled') == 'y')
      {
         $site_status = ($PREFS->ini('is_site_on') == 'y') ? 'on' : 'off' ;
         $statuses .= "
               <li class='status_$site_status'><a title='Site is ".ucfirst($site_status)."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=general_cfg' id='site_status'>Site Status</a></li>";
      }

      $statuses .= "
               <li class='status_$debug_status'><a title='".$debug_message."' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg' id='debug_status'>Debug Status</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      
      return $statuses;
   }
   
   
   
      
   
   /**
    * Add Template links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_templates($access = NULL)
   {
      global $PREFS,$DB;
      
      if ($access['is_super_admin'])
      {
         $groups = $DB->query("SELECT group_id,site_id,group_name FROM exp_template_groups WHERE site_id = '".$PREFS->ini('site_id')."' ORDER BY group_order");
      } else {
         $groups = $DB->query("SELECT tg.group_id,tg.site_id,tg.group_name
                               FROM exp_template_groups AS tg
                               JOIN exp_template_member_groups AS mg
                               ON mg.template_group_id = tg.group_id
                               WHERE mg.group_id = '".$access['group_id']."'
                               AND tg.site_id = '".$PREFS->ini('site_id')."'
                               ORDER BY tg.group_order");
      }
      
      $temps = "
      <li>
         <a class='icon no_link' id='templates' href='#'>Template Options</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Manager</strong></li>
               <li class='more'><a href='".CP_URL."?C=templates'>Manage Templates</a>
                  <div class='sub2'>
                     <ul>
                        <li><strong>View Template Groups</strong></li>
                        ";
      foreach ($groups->result as $group) {
         $temps_in_group = $DB->query("SELECT template_id,template_name,group_id FROM exp_templates WHERE group_id = '".$group['group_id']."'");
         
         $temps .= "<li class='more'><a href='".CP_URL."?C=templates&M=edit_templates&tgpref=".$group['group_id']."'>".$group['group_name']."</a>";
         if ($temps_in_group->num_rows > 0) {
            $temps .= "<div class='sub2'>
                           <ul>
                              <li><strong>Edit Templates</strong></li>
                                 ";
            foreach ($temps_in_group->result as $template) {
               $temps .= "<li><a href='".CP_URL."?C=templates&M=edit_template&tgpref=".$template['group_id']."&id=".$template['template_id']."'>".$template['template_name']."</a></li>
                                 ";
            }
            $temps.="</ul>
                        <span class='arrow'>
                     </div>";
         }
         $temps .= "</li>";
      }
                              
      $temps .= "</ul>
                     <span class='arrow'></span>
                  </div>
               </li>   
               <li><a href='".CP_URL."?C=templates&amp;M=template_prefs_manager'>Manage Preferences</a></li>
               <li><a href='".CP_URL."?C=templates&amp;M=global_variables'>Global Variables</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
      
      return $temps;
   }


   
   
   
   
   /**
    * Add Cache clearing links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_cache($access = NULL)
   {
      
      // Not quite ready for prime time            
      // $toolbar .= "
      //       <li><a href='#'>Page Cache</a></li>
      //       <li><a href='#'>Tag Cache</a></li>
      //       <li><a href='#'>Database Cache</a></li>
      //       <li><a href='#'>SQL Cache</a></li>
      //       <li><a href='#'>Relationships Cache</a></li>
      //       <li><a href='#'>All Cache</a></li>";
      
      return "
      <li>
         <a class='icon no_link' id='cache' href='#'>Clear Cache</a>
         <div class='sub'>
            <ul>
               <li><strong>Clear Cache</strong></li>
               <li><a href='".CP_URL."?C=admin&amp;M=utilities&amp;P=clear_cache_form'>Clear all Cache</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
   }
   
   
   
   
   
   /**
    * Add Add-Ons direct links
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_addons($access = NULL)
   {
      $addons = '';
      
      $addons = "
          <li>
               <a class='icon no_link' id='addons' href='#'>Addons</a>
               <div class='sub visible'>
                  <ul>
                     <li><strong>Add-ons</strong></li>";

      if ($access['is_super_admin'] || ($access['can_access_admin'] == 'y' && $access['can_admin_utilities'] == 'y'))
      {
            $addons .= "
                     <li class='more'>
                     <a id='extensions' href='".CP_URL."?C=admin&M=utilities&P=extensions_manager'>Extensions</a>
      ";
            $addons .= $this->create_ext_menu();

            $addons .="
                     </li>
                     <li class='more'>
                        <a id='plugins' href='".CP_URL."?C=admin&amp;M=utilities&amp;P=plugin_manager'>Plugins</a>
      ";
               $addons .= $this->create_pi_menu();

               $addons .= "
                     </li>";
      }
      if ($access['is_super_admin'] || ($access['can_access_modules']))
      {
               $addons .= "
                     <li class='more'>
                        <a id='modules' href='".CP_URL."?C=modules'>Modules</a>
      ";
               $addons .= $this->create_mod_menu($access['group_id']);

               $addons .= "
                        </li>";
      }
               $addons .= "
                  </ul>
                  <span class='arrow'></span>
               </div>
            </li>";
            
      return $addons;
   }
   
   
   
   
   
   /**
    * Add Template Debugging preference link
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_temp_debug($access = NULL)
   {
      global $PREFS;
      $template_debugging = ($PREFS->core_ini['template_debugging'] == 'y') ? 'on' : 'off' ;
      return "
      <li>
         <a class='icon no_link' id='temp_debug' href='#'>Template Debugging</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Debugging</strong></li>
               <li class='status_".$template_debugging."'><a title='Click to toggle' href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg'>Currently ".ucfirst($template_debugging)."</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
   }
   
   
   
   
   
   /**
    * Add SQL Query preference link
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_sql_queries($access = NULL)
   {
      global $PREFS;
      $show_queries = ($PREFS->core_ini['show_queries'] == 'y') ? 'on' : 'off' ;
      return "
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
   
   
   
   
   /**
    * Add links for adding EE elements like entries, templates, etc
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_add($access = NULL)
   {
      return "
      <li>
         <a class='icon' id='publish'>Add</a>
         <div class='sub'>
            <ul>
               <li><strong>Add</strong></li>
               <li><a href='#'>Coming........now you know it's on the way!</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>";
   }


   
   
   
   
   
   /**
    * Add load stats to toolbar
    *
    * @param      user access
    * @access     private
    * @since      1.1.0
    * @return     string
    */
   private function piece_load_stats($access = NULL)
   {
      return "
      

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
   </ul>";
      
   }
   
   
   
   
   
   /**
    * Add a divider to the toolbar
    *
    * @param      string   $posiiton
    * @access     private
    * @return     string
    */
   private function piece_divider($access = NULL, $position = 'standard')
   {
      $divider = '';
      
      if ($position != 'front')
      {
         $divider .= "  </ul>";
      }
      
      $divider .= "
   <div class='divider'></div>
";

      if ($position != 'front')
      {
         $divider .= "  <ul>";
      }
      
      return $divider;
      
   }
   
   
   
   
   
   /**
    * Register a new Addon Source
    *
    * @param   array $sources The existing sources
    * @return  array The new source list
    * @since   version 1.0.0
    */
   public function lg_addon_update_register_source($sources)
   {
       global $EXT;
       
       if ($EXT->last_call !== FALSE)
       {
          $sources = $EXT->last_call;
       }

       if ($this->settings['check_for_extension_updates'] == 'yes')
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
   public function lg_addon_update_register_addon($addons)
   {
   	global $EXT;
   	
   	if ($EXT->last_call !== FALSE)
   	{
   	   $addons = $EXT->last_call;
   	}

   	if ($this->settings['check_for_extension_updates'] == 'yes')
   	{
   		$addons[ER_DTB_name] = ER_DTB_version;
   	}
   	return $addons;
   }
   
}
// END class 

/* End of file ext.er_developer_toolbar.php */
/* Location: ./system/extensions/ext.er_developer_toolbar.php */