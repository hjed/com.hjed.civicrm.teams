<?php

require_once 'teams.civix.php';
use CRM_Teams_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function teams_civicrm_config(&$config) {
  _teams_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function teams_civicrm_xmlMenu(&$files) {
  _teams_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function teams_civicrm_install() {
  _teams_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function teams_civicrm_postInstall() {
  _teams_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function teams_civicrm_uninstall() {
  _teams_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function teams_civicrm_enable() {
  _teams_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function teams_civicrm_disable() {
  _teams_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function teams_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _teams_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function teams_civicrm_managed(&$entities) {
  _teams_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function teams_civicrm_caseTypes(&$caseTypes) {
  _teams_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function teams_civicrm_angularModules(&$angularModules) {
  _teams_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function teams_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _teams_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function teams_civicrm_entityTypes(&$entityTypes) {
  _teams_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function teams_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function teams_civicrm_navigationMenu(&$menu) {
  _teams_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _teams_civix_navigationMenu($menu);
} // */

/**
 * Implementation of hook_civicrm_post
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function teams_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  // there's an empty character at the start of team that causes problems
  if($objectName == 'Organization' && $op == 'create' && strpos($objectRef->contact_sub_type, "Team") != false ) {
    teams_create_team_group($objectId, $objectRef);
  } else if($objectName == 'Relationship' && ($op == 'edit' || $op == 'create')) {
    if(CRM_Teams_Helper::get_team_member_relationship_id() == $objectRef->relationship_type_id) {
      CRM_Teams_Helper::link_contact_to_team_from_relationship($objectRef);
    }
  }
  // TODO: handle delete
}

function teams_civicrm_custom($op, $groupId, $entityID, &$params) {
  if($op == 'edit' || $op == 'create') {
    if($groupId == teams_get_team_membership_group_id()) {
      CRM_Teams_Helper::link_contact_to_team($entityID, $params);
    }
  }
}


function teams_create_team_group($objectId, &$objectRef) {

  $params = array(
    'name_a_b' => "Member",
    'contact_sub_type_a' => "Team"
  );
  $result = civicrm_api3('RelationshipType', 'get', $params);
  $params = array(
    'form_values' => array(
      'relation_type_id' => key($result['values']).'_b_a',
      'relation_target_name' => $objectRef->legal_name,
      'operator'=>'and'
    ),
    'api.Group.create' => array(
      'name' => $objectRef->legal_name,
      'title' => $objectRef->legal_name,
      'saved_search_id' => '$value.id',
      'is_active' => 1,
      'visibility' => 'User and User Admin Only',
      'is_hidden' => 0,
      'is_reserved' => 0,
    ),
  );

  try{
    $result = civicrm_api3('SavedSearch', 'create', $params);
    teams_set_group_team_link(array_values($result['values'])[0]['api.Group.create']['id'],$objectId);
  } catch (CiviCRM_API3_Exception $e) {
    // Handle error here.
    $errorMessage = $e->getMessage();
    $errorCode = $e->getErrorCode();
    $errorData = $e->getExtraParams();
    return array(
      'is_error' => 1,
      'error_message' => $errorMessage,
      'error_code' => $errorCode,
      'error_data' => $errorData,
    );
  }

  return $result;
}

function teams_get_group_team_link_custom_field_id() {
  $params = array(
    'column_name' => 'team_36'
  );
  $result = civicrm_api3('CustomField', 'get', $params);
  return key($result['values']);
}

function teams_set_group_team_link($groupId, $team_id) {
  $params = array(
    'entity_id' => $groupId,
    'custom_' . teams_get_group_team_link_custom_field_id() => $team_id
  );
  $result = civicrm_api3('CustomValue', 'create', $params);
}

function teams_get_team_membership_group_id() {
  $result = civicrm_api3('CustomGroup', 'get', [
    'sequential' => 1,
    'return' => ["id"],
    'table_name' => "civicrm_value_team_membership",
  ]);
  return $result["id"];
}