<?php 
/*
Plugin Name: Embedded Videos
Version: auto
Description: Add videos from Dailymotion, Youtube, Vimeo, Wideo and Wat.
Plugin URI: auto
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

global $prefixeTable;

define('GVIDEO_ID',      basename(dirname(__FILE__)));
define('GVIDEO_PATH',    PHPWG_PLUGINS_PATH . GVIDEO_ID . '/');
define('GVIDEO_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . GVIDEO_ID);
define('GVIDEO_TABLE',   $prefixeTable.'image_video');
define('GVIDEO_VERSION', 'auto');


add_event_handler('init', 'gvideo_init');
add_event_handler('picture_pictures_data', 'gvideo_prepare_picture');

if (defined('IN_ADMIN'))
{
  add_event_handler('get_admin_plugin_menu_links', 'gvideo_admin_menu');
  add_event_handler('tabsheet_before_select','gvideo_tab', EVENT_HANDLER_PRIORITY_NEUTRAL+10, 2); 
}

add_event_handler('delete_elements', 'gvideo_delete_elements');

include_once(GVIDEO_PATH . 'include/events.inc.php');


/**
 * init
 */
function gvideo_init()
{
  global $conf;
  
  include_once(GVIDEO_PATH . 'maintain.inc.php');
  $maintain = new gvideo_maintain(GVIDEO_ID);
  $maintain->autoUpdate(GVIDEO_VERSION, 'install');
  
  $conf['gvideo'] = unserialize($conf['gvideo']);
}

/**
 * admin plugins menu
 */
function gvideo_admin_menu($menu) 
{
  $menu[] = array(
    'NAME' => 'Embedded Videos',
    'URL' => GVIDEO_ADMIN,
    );
  return $menu;
}

/**
 * special tabs
 */
function gvideo_tab($sheets, $id)
{
  if ($id != 'photo') return $sheets;
  
  $query = '
SELECT *
  FROM '.GVIDEO_TABLE.'
  WHERE picture_id = '.$_GET['image_id'].'
;';
  $result = pwg_query($query);

  if (!pwg_db_num_rows($result)) return $sheets;
  
  global $gvideo;
  $gvideo = pwg_db_fetch_assoc($result);
  
  $sheets['gvideo'] = array(
    'caption' => l10n('Video properties'),
    'url' => GVIDEO_ADMIN.'-photo&amp;image_id='.$_GET['image_id'],
    );
  unset($sheets['coi'], $sheets['update']);
  
  return $sheets;
}
