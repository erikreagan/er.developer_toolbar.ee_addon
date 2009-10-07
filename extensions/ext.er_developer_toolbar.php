<?php

/**
 * ER Developer Toolbar
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERDeveloperToolbar
 * @version 0.9.2
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er_developer_toolbar/
 */


if ( ! defined('EXT')) exit('Invalid file request');


class Er_developer_toolbar
{
   
   var $settings = array();

   var $name = 'ER Developer Toolbar';
   var $version = '0.9.2';
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
      
      $settings             = array();    
      $settings['groups']   = array('ms', $member_groups_array, '1');
      $settings['position'] = array('s', array('top hor'=>"Top",'right vert'=>"Right",'bot hor'=>"Bottom",'left vert'=>"Left"),'top hor');
      $settings['check_for_extension_updates'] = array('r', array('yes' => "yes", 'no' => "no"), 'yes');


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
         'groups'                      => array('1'),
         'position'                    => 'top hor',
         'check_for_extension_updates' => 'yes',
         );
      
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
      global $EXT, $IN, $PREFS;

      if ( ! in_array($s->userdata['group_id'], $this->settings['groups']) )
      {
         $IN->global_vars['er_developer_toolbar']      = '';
         $IN->global_vars['er_developer_toolbar_head'] = '';
         
         return;
      }
      
      if ($EXT->last_call !== FALSE)
		{
			$s =& $EXT->last_call;
		}
      
      $IN->global_vars['er_developer_toolbar_head'] = "
   <link rel='stylesheet' href='".$PREFS->core_ini['theme_folder_url']."toolbar/style.css' type='text/css' title='no title' charset='utf-8' />
";
      $IN->global_vars['er_developer_toolbar'] = $this->_create_toolbar();
      
   }
   
   
   
   /**
    * Create Developer toolbar
    * 
    * @access Private
    * @return string
    */
   function _create_toolbar()
   {
      global $DB, $PREFS, $SESS, $LANG;
      
      define('CP_URL',$PREFS->core_ini['cp_url']);

      $toolbar = '';
      

      
      
      // Get settings for site and debug and set CSS classes
      $system_status = ($PREFS->core_ini['is_system_on'] == 'y') ? 'on' : 'off' ;
      $template_debugging = ($PREFS->core_ini['template_debugging'] == 'y') ? 'on' : 'off' ;
      $show_queries = ($PREFS->core_ini['show_queries'] == 'y') ? 'on' : 'off' ;
      
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
<div id='er_developer_toolbar' class='".$this->settings['position']."'>";

   //    This one isn't quite ready for prime time
   //    $toolbar .= "
   // <div class='icon' id='move'></div>";
   
      $toolbar .="
   <p title='ER Developer Toolbar: by Erik Reagan' class='toolbar_heading'>Developer Toolbar</p>

   <div class='divider'></div>      

   <ul>
      <li>
         <a class='icon' id='home' title='CP Home' href='".CP_URL."'>CP Home</a>
         <div class='sub'>
            <ul>
               <li><strong>Control Panel Home</strong></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon' id='account' title='My Account' href='".CP_URL."?C=myaccount'>My Account</a>
         <div class='sub'>
            <ul>
               <li><strong>My Account</strong></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon' id='logout' title='Logout' href='".CP_URL."?C=logout'>Logout</a>
         <div class='sub'>
            <ul>
               <li><strong>Logout</strong></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
   </ul>


   <div class='divider'></div>


   <ul>
      <li>
         <a class='icon' id='statuses' href='#'>Statuses</a>
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
      </li>
      <li>
         <a class='icon' id='templates' title='Template Manager' href='".CP_URL."?C=templates'>Template Manager</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Manager</strong></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon' id='cache' title='Clear Cache' href='".CP_URL."?C=admin&amp;M=utilities&amp;P=clear_cache_form'>Clear Cache</a>
         <div class='sub'>
            <ul>
               <li><strong>Clear Cache</strong></li>";
         //       Not quite ready for prime time
         //       <li><a href='#'>Page Cache</a></li>
         //       <li><a href='#'>Tag Cache</a></li>
         //       <li><a href='#'>Database Cache</a></li>
         //       <li><a href='#'>SQL Cache</a></li>
         //       <li><a href='#'>Relationships Cache</a></li>
         //       <li><a href='#'>All Cache</a></li>
      $toolbar .= "
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
   </ul>


   <div class='divider'></div>


   <ul>
      <li>
         <a class='icon' id='addons' href='#'>Addons</a>
         <div class='sub visible'>
            <ul>
               <li><strong>Add-ons</strong></li>
               <li><a id='extensions' href='".CP_URL."?C=admin&M=utilities&P=extensions_manager'>Extensions</a></li>
               <li><a id='plugins' href='".CP_URL."?C=admin&amp;M=utilities&amp;P=plugin_manager'>Plugins</a></li>
               <li><a id='modules' href='".CP_URL."?C=modules'>Modules</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon' id='temp_debug' href='#'>Template Debugging</a>
         <div class='sub'>
            <ul>
               <li><strong>Template Debugging</strong></li>
               <li class='status_".$template_debugging."'><a href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg'>Currently ".ucfirst($template_debugging)."</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
      <li>
         <a class='icon' id='sql' href=''>Display SQL Queries</a>
         <div class='sub'>
            <ul>
               <li><strong>Display SQL Queries</strong></li>
               <li class='status_".$show_queries."'><a href='".CP_URL."?C=admin&amp;M=config_mgr&amp;P=output_cfg'>Currently ".ucfirst($show_queries)."</a></li>
            </ul>
            <span class='arrow'></span>
         </div>
      </li>
   </ul>


   <div class='divider'></div>

";

   // Not quite ready for prime time either...
   // <ul>
   //    <li>
   //       <a class='icon' id='edit' title='Edit Options' href='#'>Edit Options</a>
   //       <div class='sub'>
   //          <ul>
   //             <li><strong>Edit Options</strong></li>
   //             <li><a class='entry' href='#'>Edit <em>Lorem ipsum dolor sit</em></a></li>
   //             <li><a class='template' href='#'>Edit embeds/.masthead</a></li>
   //             <li><a class='template' href='#'>Edit site/index</a></li>
   //             <li><a class='template' href='#'>Edit embeds/.footer</a></li>
   //          </ul>
   //          <span class='arrow'></span>
   //       </div>
   //    </li>
   // </ul>

      $toolbar .= "
   <ul id='clock'>
      <li>
         <a class='icon' id='utility' title='Good to Know' hred='#'>Good to Know</a>
         <div class='sub'>
            <ul>
               <li><strong>Good to Know</strong></li>
               <li title='Elapsed Time' id='toolbar_elapsed_time'>{elapsed_time} seconds</li>
               <li title='Total Queries' id='toolbar_queries'>{total_queries} queries</li>
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