<?php
/*
 * @version $Id: setup.php 313 2011-12-19 09:39:58Z remi $
 -------------------------------------------------------------------------
 treeview - TreeView browser plugin for GLPI
 Copyright (C) 2003-2012 by the treeview Development Team.

 https://forge.indepnet.net/projects/treeview
 -------------------------------------------------------------------------

 LICENSE

 This file is part of treeview.

 treeview is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 treeview is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with treeview. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginTreeviewProfile extends CommonDBTM {

static $rightname = "profile";
   //static $rightname = 'plugin_treeview';
   static function getTypeName($nb = 0) {
      return __('Tree view', 'treeview');
   }
   
      static function getMenuName() {
      return __('Tree view', 'treeview');
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Profile' 
         && $item->getField('interface')!='helpdesk') {
         return PluginTreeviewProfile::getTypeName(2);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      global $CFG_GLPI;

      if ($item->getType()=='Profile') {
         $ID = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, 
                                    array('treeview' => 0));
         $prof->showForm($ID);
      }
      
      return true;
   }
   
   function createAccess($profile) {

      return $this->add(array('id'   => $profile->getField('id'),
                              'name' => addslashes($profile->getField('name'))));
   }

   function showForm($profiles_id=0, $openform=TRUE, $closeform=TRUE) {

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, array('canedit'       => $canedit,
                                                        'default_class' => 'tab_bg_2',
                                                        'title'         => __('General')));
      
      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";

   }
   
   static function getAllRights($all = false) {
      $rights = array(array('itemtype'  => 'PluginTreeviewConfig',
                            'label'     => _n('Treeview', 'Treeview', 2, 'treeview'),
                            'field'     => 'plugin_treeview'
                ));
      
      return $rights;
   }
     /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights", 
                                  "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }
   }


    /**
   * Initialize profiles, and migrate it necessary
   */
   static function changeProfile() {
      global $DB;

      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights` 
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."' 
                              AND `name` LIKE '%plugin_treeview%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights']; 
      }
      //   require 'preference.class.php';

      $Pref = new PluginTreeviewPreference();
      $pref_value = $Pref->checkPreferenceValue(Session::getLoginUserID());
      if ($pref_value==1) {
         $_SESSION["glpi_plugin_treeview_preference"] = 1;
      } else {
         unset($_SESSION["glpi_plugin_treeview_preference"]);
      }
   }

   static function createFirstAccess($profiles_id) {
      self::addDefaultProfileInfos($profiles_id, 
                                   array('plugin_treeview' => READ), true);


   }
   static function cleanProfiles(Profile $prof) {

      $plugprof = new self();
      $plugprof->delete(array('id' => $prof->getField("id")));
   }
   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;
      
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'") && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   
}
?>