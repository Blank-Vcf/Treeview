<?php

function plugin_treeview_install()
{
   global $DB;

   // not installed

   if (!TableExists("glpi_plugin_treeview_configs")) {
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_treeview_configs` (
                  `id` int(11) NOT NULL auto_increment,
                  `target` varchar(255) NOT NULL default 'right',
                  `folderLinks` tinyint(1) NOT NULL default '0',
                  `useSelection` tinyint(1) NOT NULL default '0',
                  `useLines` tinyint(1) NOT NULL default '0',
                  `useIcons` tinyint(1) NOT NULL default '0',
                  `closeSameLevel` tinyint(1) NOT NULL default '0',
                  `itemName` int(11) NOT NULL default '0',
                  `locationName`  int(11) NOT NULL default '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";
      $DB->query($query) or die($DB->error());
      $query = "INSERT INTO `glpi_plugin_treeview_configs`
                     (`id`, `target`, `folderLinks`, `useSelection`, `useLines`, `useIcons`,
                      `closeSameLevel`, `itemName`, `locationName`)
                VALUES ('1','right','1','1','1','1','0', '3', '2');";
      $DB->query($query) or die($DB->error());
      $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_treeview_preferences` (
                  `id` int(11) NOT NULL auto_increment,
                  `users_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
                  `show_on_load` int(11) NOT NULL default '0',
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ";
      $DB->query($query) or die($DB->error());
   }

   include_once (GLPI_ROOT . "/plugins/treeview/inc/profile.class.php");

   PluginTreeviewProfile::initProfile();
   PluginTreeviewProfile::createfirstAccess($_SESSION['glpiactiveprofile']['id']);
   return true;
}

function plugin_treeview_uninstall()
{
   global $DB;
   $tables = array(
      "glpi_plugin_treeview_display",
      "glpi_plugin_treeview_displayprefs",
      "glpi_plugin_treeview_configs",
      "glpi_plugin_treeview_profiles",
      "glpi_plugin_treeview_preference",
      "glpi_plugin_treeview_preferences"
   );
   foreach($tables as $table) {
      $query = "DROP TABLE IF EXISTS `$table`;";
      $DB->query($query) or die($DB->error());
   }
}

// Hook done on before update item case

function plugin_item_update_treeview($item)
{
   if (in_array('locations_id', $item->updates)) {
      echo "<script type='text/javascript'>parent.left.location.reload(true);</script>";
   }
}

/*
* non affichage des objets mis à la corbeille
*/

function plugin_treeview_reload($item)
{
   echo "<script type='text/javascript'>parent.left.location.reload(true);</script>";
}

function plugin_change_entity_Treeview()
{
   if ($_SESSION['glpiactiveprofile']['interface'] == 'central' && (isset($_SESSION["glpi_plugin_treeview_loaded"]) && $_SESSION["glpi_plugin_treeview_loaded"] == 1)) {
      echo "<script type='text/javascript'>parent.left.location.reload(true);</script>";
   }
}

?>