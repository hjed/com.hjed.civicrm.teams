<?php
use CRM_Teams_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 * Heavily borrowed from CiviVolunter
 */
class CRM_Teams_Upgrader extends CRM_Teams_Upgrader_Base {

  private static $TEAM_CONTACT_TYPE = "Team";

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Create relationships
   */
  public function install() {
    $teamContactTypeId = $this->create_team_contact_type();
    $this->create_relationships();
    
    $smarty->assign('team_contact_type_id', $teamContactTypeId);

    $customIDs = $this->findCustomGroupValueIDs();
    $smarty->assign('customIDs', $customIDs);
    $this->executeCustomDataTemplateFile("team_contact.xml.tpl");
  }


  /**
   * Creates the Team Contact Type
   * Heavily inspired by https://github.com/civicrm/org.civicrm.volunteer/blob/master/CRM/Volunteer/Upgrader.php
   * @throws CiviCRM_API3_Exception
   */
  public function create_team_contact_type() {
    $id = NULL;
    $get = civicrm_api3('ContactType', 'get', array(
      'name' => self::$TEAM_CONTACT_TYPE,
      'return' => 'id',
      'sequential' => 1
    ));

    // if it already exists return it
    if($get['count']) {
      $id = $get['values'][0]['id'];
    } else {
      $create = civicrm_api3('ContactType', 'create', array(
        'label' => ts('Team', array('domain' => 'com.hjed.civicrm.teams')),
        'name' => self::$TEAM_CONTACT_TYPE,
        'parent_id' => civicrm_api3('ContactType', 'getvalue', array(
          'name' => 'Organization',
          'return' => 'id',
        )),
      ));
      if (CRM_Utils_Array::value('is_error', $create)) {
        CRM_Core_Error::debug_var('contactTypeResult', $create, TRUE, TRUE, 'com.hjed.civicrm.teams');
        throw new CRM_Core_Exception('Failed to register contact type');
      }
      $id = $create['id'];
    }
    return (int) $id;
  }

  public function create_relationships() {
    $result = civicrm_api3('RelationshipType', 'create', [
      'name_a_b' => "Team Lead",
      'name_b_a' => "Team Lead of",
      'description' => "The person leading the team",
      'contact_type_b' => "Individual",
      'contact_sub_type_a' => "Team",
      'is_reserved' => 1,
    ]);
    if (CRM_Utils_Array::value('is_error', $create)) {
      CRM_Core_Error::debug_var('teamlead', $create, TRUE, TRUE, 'com.hjed.civicrm.teams');
      throw new CRM_Core_Exception('Failed to register team lead relationship');
    }
    $result = civicrm_api3('RelationshipType', 'create', [
      'name_a_b' => "Member",
      'name_b_a' => "Member of",
      'description' => "A member of the team",
      'contact_type_b' => "Individual",
      'contact_sub_type_a' => "Team",
      'is_reserved' => 1,
    ]);
    if (CRM_Utils_Array::value('is_error', $create)) {
      CRM_Core_Error::debug_var('teammember', $create, TRUE, TRUE, 'com.hjed.civicrm.teams');
      throw new CRM_Core_Exception('Failed to register team member relationship');
    }
  }

  public function executeCustomDataTemplateFile($relativePath) {
    $smarty = CRM_Core_Smarty::singleton();
    $xmlCode = $smarty->fetch($relativePath);
    $xml = simplexml_load_string($xmlCode);
    require_once 'CRM/Utils/Migrate/Import.php';
    $import = new CRM_Utils_Migrate_Import();
    $import->runXmlElement($xml);
    return TRUE;
  }

  public function findCustomGroupValueIDs() {
    $result = array();
    $query = "SELECT `table_name`, `AUTO_INCREMENT` FROM `information_schema`.`TABLES`
      WHERE `table_schema` = DATABASE()
      AND `table_name` IN ('civicrm_custom_group', 'civicrm_custom_field')";
    $dao = CRM_Core_DAO::executeQuery($query);
    while ($dao->fetch()) {
      $result[$dao->table_name] = (int) $dao->AUTO_INCREMENT;
    }
    return $result;
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   *
  public function postInstall() {
    $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
      'return' => array("id"),
      'name' => "customFieldCreatedViaManagedHook",
    ));
    civicrm_api3('Setting', 'create', array(
      'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
    ));
  }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   *
  public function uninstall() {
   $this->executeSqlFile('sql/myuninstall.sql');
  }

  /**
   * Example: Run a simple query when a module is enabled.
   *
  public function enable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a simple query when a module is disabled.
   *
  public function disable() {
    CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   *
  public function upgrade_4200() {
    $this->ctx->log->info('Applying update 4200');
    CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
    CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
    return TRUE;
  } // */


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4201() {
    $this->ctx->log->info('Applying update 4201');
    // this path is relative to the extension base dir
    $this->executeSqlFile('sql/upgrade_4201.sql');
    return TRUE;
  } // */


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4202() {
    $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

    $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
    $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
    $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
    return TRUE;
  }
  public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  public function processPart3($arg5) { sleep(10); return TRUE; }
  // */


  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
  public function upgrade_4203() {
    $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

    $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
    $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
    for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
      $endId = $startId + self::BATCH_SIZE - 1;
      $title = E::ts('Upgrade Batch (%1 => %2)', array(
        1 => $startId,
        2 => $endId,
      ));
      $sql = '
        UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
        WHERE id BETWEEN %1 and %2
      ';
      $params = array(
        1 => array($startId, 'Integer'),
        2 => array($endId, 'Integer'),
      );
      $this->addTask($title, 'executeSql', $sql, $params);
    }
    return TRUE;
  } // */

}
