<?php

/**
 * ER Developer Pane
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERDeveloperPane
 * @version 0.5.0
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er_developer_pane/
 */


if ( ! defined('EXT')) exit('Invalid file request');


class Er_developer_pane
{
   
   var $settings = array();

   var $name = 'ER Developer Pane';
   var $version = '0.5.0';
   var $description = 'Adds a developer toolbox pane as a global variable available within your templates';
   var $settings_exist = 'y';
   var $docs_url = '';


   /**
   * PHP4 Constructor
   *
   * @see __construct()
   */

   function Er_developer_pane($settings='')
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
      foreach ($member_groups->result as $group) {
         $member_groups_array[$group['group_id']] = $group['group_title'];
      }      
      
      $settings = array();    
      $settings['groups'] = array('ms', $member_groups_array, '1');

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
      
      // By default we want to restrict the settings to the super admin group which is group_id '1'
      $settings = array();
      $settings['groups'] = array('1');

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
                   WHERE class = 'Er_developer_pane'");
   }
   
   
   
   /**
   * Disables the extension the extension and deletes settings from DB
   */
   function disable_extension()
   {
       global $DB;
       $DB->query("DELETE FROM exp_extensions WHERE class = 'Er_developer_pane'");
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
         $IN->global_vars['er_developer_pane'] = '';
         
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
      
      $r = $this->_create_pane();
      
      $IN->global_vars['er_developer_pane'] = $r;
      
   }
   
   
   
   /**
    * Create Developer Pane
    * 
    * @access Private
    * @return string
    */
   function _create_pane()
   {
      
      $pane = "<div style='position: absolute; top: 0; right: 0; position: fixed; background-color: #000; color: #fff; font-size: 14px; padding: 5px;'>
              {elapsed_time} seconds / {total_queries} queries
          </div>";
      
      return $pane;
      
   }
   
}
// END class 

/* End of file ext.er_developer_pane.php */
/* Location: ./system/extensions/ext.er_developer_pane.php */