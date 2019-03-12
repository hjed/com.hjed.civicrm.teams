<?php
class CRM_Teams_Helper {

  static $adding_relationship = false;
  /**
   * Creates a relationship when the team custom field is set and removes it when it is cleared
   * @param $contactId the contactId to link
   * @param $params the custom field params
   * @throws CiviCRM_API3_Exception if the api fails
   */
  static function link_or_unlink_contact_to_team_from_custom_field($contactId, &$params) {
    if(self::$adding_relationship) {
      return;
    }
    self::$adding_relationship = true;
    try {
      $clearedValueFound = false;
      $validTeamsFound = array();
      foreach ($params as $param) {
        if ($param["column_name"] == "individual_to_team") {
          if ($param['value'] != null) {
            $teamId = $param['value'];
            $validTeamsFound[] = intval($teamId);
            $relationshipId = self::get_team_member_relationship_id();
            // check the relationship doesn't already exist
            $result = civicrm_api3('Relationship', 'get', [
              'sequential' => 1,
              'contact_id_a' => $teamId,
              'contact_id_b' => $contactId,
              'relationship_type_id' => $relationshipId,
            ]);
            if ($result['count'] == 0) {
              $input = array(
                'contact_id_a' => intval($teamId),
                'contact_id_b' => intval($contactId),
                'relationship_type_id' => $relationshipId
              );
              $otherinput['test'] = 1;
              // create it
              $result = civicrm_api3('Relationship', 'create', $input);
            } else {
            }
          } else {
            // we need to remove any bad existing ones
            $clearedValueFound = true;
          }
        }
      }

      if($clearedValueFound) {
        $teamMemberships = civicrm_api3(
          'Relationship',
          'get',
          [
            'relationship_type_id' => self::get_team_member_relationship_id(),
            'contact_id_b' => $contactId
          ]
        )['values'];

        // the above only gets changed values, so we need to check the rest of the teams
        $links = self::get_team_membership_cf_values($contactId);

        // check if it exists
        foreach ($links as $key => $value) {
          // the api mixes actual values with metadata
          if (is_numeric($key)) {
            $validTeamsFound[] = $value;
          }
        }
        foreach ($teamMemberships as $key => $rel)  {
          if(!in_array(intval($rel["contact_id_a"]), $validTeamsFound)) {
            civicrm_api3(
              'Relationship',
              'delete',
              [
                'id' => $rel['id']
              ]
            );
          }
        }
      }
    } finally {
      self::$adding_relationship = false;
    }
  }

  static function unlink_contact_to_team_from_relationship(&$relationship) {

    $links = self::get_team_membership_cf_values($relationship->contact_id_b);

    // check if it exists
    foreach ($links as $key => $value) {
      // the api mixes actual values with metadata
      if (is_numeric($key)) {
        if (intval($value) == intval($relationship->contact_id_a)) {
          $params = array(
            'entity_id' => $relationship->contact_id_b,
            'custom_' . self::get_team_membership_custom_field_id() . ':' . $key => ""
          );
          $results = civicrm_api3(
            'CustomValue',
            'create',
            $params
          );

        }
      }
    }


  }
  static function link_contact_to_team_from_relationship(&$relationship) {
    if(self::$adding_relationship) {
      return;
    }
    self::$adding_relationship = true;
    try {
      $links = self::get_team_membership_cf_values($relationship->contact_id_b);

      // check if it exists
      foreach ($links as $key => $value) {
        // the api mixes actual values with metadata
        if (is_numeric($key)) {
          if (intval($value) == intval($relationship->contact_id_a)) {
            return;
          }
        }
      }

      // create it
      $params = array(
          'entity_id' => $relationship->contact_id_b,
          'custom_' . self::get_team_membership_custom_field_id() => $relationship->contact_id_a
      );
      $results = civicrm_api3(
        'CustomValue',
        'create',
        $params
      );
    } finally {
      self::$adding_relationship = false;
    }

  }
  private static function get_team_membership_cf_values($contactId) {

    $contactCustomFields = civicrm_api3(
      'CustomValue',
      'get',
      [
        'entity_id' => $contactId
      ]
    );

    $links = $contactCustomFields['values'][self::get_team_membership_custom_field_id()];

    return $links;
  }

  /**
   * @return mixed the id of the relationship type for team membership
   * @throws CiviCRM_API3_Exception
   */
  static function get_team_member_relationship_id() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => "Member",
    ]);
    return $result["id"];
  }

  static function get_team_membership_custom_field_id() {
    $result = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ["id"],
      'custom_group_id' => "Team_Memberships",
      'column_name' => "individual_to_team",
    ]);
    return $result["id"];
  }

  static function get_team_membership_group_id() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ["id"],
      'table_name' => "civicrm_value_team_membership",
    ]);
    return $result["id"];
  }
}
?>