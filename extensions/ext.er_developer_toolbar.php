<?php

/**
 * ER Developer Toolbar
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERDeveloperToolbar
 * @version 0.5.0
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er_developer_toolbar/
 */


if ( ! defined('EXT')) exit('Invalid file request');


class Er_developer_toolbar
{
   
   var $settings = array();

   var $name = 'ER Developer Toolbar';
   var $version = '0.5.0';
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
      $this->settings = $settings;
   }


   /**
   * Configuration for the extension settings page
   *
   * @return array
   */
   function settings()
   {
      global $LANG, $DB, $PREFS;
      
      // Grab the member groups from our current site
      $member_groups = $DB->query("SELECT group_id,site_id,group_title FROM exp_member_groups WHERE `site_id` = " . $PREFS->ini("site_id"));

      // Create an array of our member groups in the format that $settings needs
      foreach ($member_groups->result as $group)
      {
         $member_groups_array[$group['group_id']] = $group['group_title'];
      }      
      
      $settings = array();    
      $settings['groups'] = array('ms', $member_groups_array, '1');
      $settings['show_user'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
      $settings['show_templates'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
      $settings['show_extensions'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
      $settings['show_plugins'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
      $settings['show_modules'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');
      $settings['css'] = array('t','','');

      return $settings;
   }
   
   /**
   * Activates the extension
   *
   * @return bool
   */
   function activate_extension()
   {
      global $DB;
      
      // By default we want to restrict the settings to the Super Admin group which is group_id '1'
      $settings = array(
         'groups'          => array('1'),
         'show_user'       => 'y',
         'show_templates'  => 'n',
         'show_extensions' => 'y',
         'show_plugins'    => 'y',
         'show_modules'    => 'n',
         'css'             => '
#er_developer_toolbar {
   position: fixed;
   display: block;
   z-index: 9999999999;
   width: 98%;
   left: 0px;
   top: 0px;
   height: 10px;
   font: 12px Arial, sans-serif;
   padding: 10px 1% 12px;
   border-bottom: 2px solid #336699;
   background: #fff;
   color: #000;
   opacity: 0.8;
   -moz-opacity: 0.8;
   filter:alpha(opacity=80);
}

#er_developer_toolbar a { color: #000; }
#er_developer_toolbar a:hover { color: #555; }

/* Reset these elements just to be safe */
#er_developer_toolbar p, #er_developer_toolbar a, #er_developer_toolbar ul, #er_developer_toolbar li { margin: 0px; padding: 0px; }


#er_developer_toolbar p.toolbar_heading {
   float: left;
   text-transform: uppercase;
   font-size: 11px;
   font-weight: bold;
   padding-right: 15px;
}
#er_developer_toolbar ul#toolbar_quick_links {
   list-style: none;
   float: left;
}
#er_developer_toolbar ul#toolbar_quick_links li { float: left; margin-right: 20px; }
#er_developer_toolbar ul#toolbar_member_data {
   list-style: none;
   float: left;
   margin-right: 20px;
   border-right: 2px solid #336699;
   border-left: 2px solid #336699;
   padding-left: 20px;
}
#er_developer_toolbar ul#toolbar_member_data li { float: left; margin-right: 20px; }
#er_developer_toolbar ul#toolbar_calc { list-style: none; float: right; font-size: 11px; }
#er_developer_toolbar ul#toolbar_calc li { float: left; margin-left: 20px; }

#er_developer_toolbar .green { color: green; }
#er_developer_toolbar .red { color: red; }
'
         );
      
      $hooks = array(
         'sessions_end' => 'sessions_end'
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
       global $DB;
       $DB->query("DELETE FROM exp_extensions WHERE class = 'Er_developer_toolbar'");
   }
   
   
   
   /**
    * Sessions End
    *
    * @param object     the session object
    **/
   function sessions_end( &$s )
   {
      global $EXT, $IN;
      
      if ( ! in_array($s->userdata['group_id'], $this->settings['groups']) )
      {
         $IN->global_vars['er_developer_toolbar'] = '';
         
         return;
      }
      
      if ($EXT->last_call !== FALSE)
		{
			$s =& $EXT->last_call;
		}
      
      // echo "<pre>";
      // print_r($s);
      // echo "</pre>";
      // exit;
      
      $r = $this->_create_toolbar();
      
      $IN->global_vars['er_developer_toolbar'] = $r;
      
   }
   
   
   
   /**
    * Create Developer toolbar
    * 
    * @access Private
    * @return string
    */
   function _create_toolbar()
   {
      global $DB, $PREFS;
      
      // Get settings for site and debug and set CSS classes
      $system_on = ($PREFS->core_ini['is_system_on'] == 'y') ? 'on' : 'off' ;
      $debug_on = ($PREFS->core_ini['debug'] > 0) ? 'on' : 'off' ;
      $system_on_class = ($system_on == 'on') ? 'green' : 'red' ;
      $debug_on_class = ($debug_on == 'on') ? 'green' : 'red' ;
      
      // Begin toolbar by grabbing the CSS settings
      $toolbar = "<style type='text/css'>
".$this->settings['css']."
</style>";
      
      // Continue toolbar with the markup
      $toolbar .= "
<div id='er_developer_toolbar'>

   <p title='by Erik Reagan' class='toolbar_heading'>Developer Toolbar</p>

   <ul id='toolbar_member_data'>";
      
      if ($this->settings['show_user'] == 'yes')
      {
         $toolbar .= "
      <li><a title='You are in the {group_title} member group' href='".$PREFS->core_ini['cp_url']."?C=myaccount'>{screen_name}</a> (<a href='".$PREFS->core_ini['cp_url']."?C=logout'>logout</a>)</li>";
      } else {
         $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=logout'>Logout</a></li>";
      }

      $toolbar .= "
   </ul>

   <ul id='toolbar_quick_links'>
      <li><strong>System Status: </strong><a title='Click to change the system status' href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=config_mgr&amp;P=general_cfg' class='".$system_on_class."'>".ucfirst($system_on)."</a></li>";
      
      
      // If we are running with MSM we want to also show if the current Site is on
      if ($PREFS->core_ini['multiple_sites_enabled'] == 'y')
      {

         $site_on = ($PREFS->core_ini['is_site_on'] == 'y') ? 'on' : 'off' ;
         $site_on_class = ($site_on == 'on') ? 'green' : 'red' ;

         $toolbar .= "
      <li><strong>Site Status: </strong><a title='Click to change the site status' href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=config_mgr&amp;P=general_cfg' class='".$site_on_class."'>".ucfirst($site_on)."</a></li>";
      
      }
      
      $toolbar .= "
      
      <li><strong>Debug Mode: </strong><a title='Click to change the debug mode' href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=config_mgr&amp;P=output_cfg' class='".$debug_on_class."'>".ucfirst($debug_on)."</a></li>";
      
      $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=utilities&amp;P=clear_cache_form'>Clear Cache</a></li>";
      
      if ($this->settings['show_templates'] == 'yes')
      {
         $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=templates'>Template Manager</a></li>";
      }
      
      if ($this->settings['show_extensions'] == 'yes')
      {
         $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=utilities&amp;P=extensions_manager'>Extensions Manager</a></li>";
      }
      
      if ($this->settings['show_plugins'] == 'yes')
      {
         $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=admin&amp;M=utilities&amp;P=plugin_manager'>Plugin Manager</a></li>";
      }
      
      if ($this->settings['show_modules'] == 'yes')
      {
         $toolbar .= "
      <li><a href='".$PREFS->core_ini['cp_url']."?C=modules'>Modules</a></li>";
      }
      
      $toolbar .= "
   </ul>

   <ul id='toolbar_calc'>
      <li title='Elapsed Time' id='toolbar_elapsed_time'>{elapsed_time} seconds</li>
      <li title='Total Queries' id='toolbar_queries'>{total_queries} queries</li>
   </ul>

</div>\n\n";
      
      return $toolbar;
      
   }
   
}
// END class 

/* End of file ext.er_developer_toolbar.php */
/* Location: ./system/extensions/ext.er_developer_toolbar.php */