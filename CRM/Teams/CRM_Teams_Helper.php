<?php
class CRM_Teams_Helper {

  const MEMBER_RELATIONSHIP_A_B = "Team Member";
  const MEMBER_RELATIONSHIP_B_A = "Team Member of";
  const TEAM_LEAD_RELATIONSHIP_A_B = "Team Lead";
  const TEAM_LEAD_RELATIONSHIP_B_A = "Team Lead of";

  static $adding_relationship = false;
  static function update_team_details($teamId, &$params) {

    foreach ($params as $param) {
      if ($param["column_name"] == "team_details_team_lead") {
        self::link_or_unlink_team_lead($teamId, $param);
      }
    }
  }

  static function link_or_unlink_team_lead($teamId, &$row) {
    if(self::$adding_relationship) {
      return;
    }
    try {
      self::$adding_relationship = true;
      if($row["value"] != null) {
        $teamLeadId = $row["value"];
        $relationshipId = self::get_team_lead_relationship_id();

        // check the relationship doesn't already exist
        $result = civicrm_api3('Relationship', 'get', [
          'sequential' => 1,
          'contact_id_a' => $teamId,
          'contact_id_b' => $teamLeadId,
          'relationship_type_id' => $relationshipId,
        ]);
        if ($result['count'] == 0) {
          $input = array(
            'contact_id_a' => intval($teamId),
            'contact_id_b' => intval($teamLeadId),
            'relationship_type_id' => $relationshipId,
            'is_permison_b_a' => 1
          );
          $otherinput['test'] = 1;
          // create it
          $result = civicrm_api3('Relationship', 'create', $input);
        }
      } else {

        $teamLead = civicrm_api3(
          'Relationship',
          'get',
          [
            'relationship_type_id' => self::get_team_lead_relationship_id(),
            'contact_id_a' => $teamId
          ]
        )['values'];

        foreach ($teamLead as $key => $rel)  {
          civicrm_api3(
            'Relationship',
            'delete',
            [
              'id' => $rel['id']
            ]
          );
        }
      }
    } finally {
      self::$adding_relationship = false;
    }
  }

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
    try {
      self::$adding_relationship = true;
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
                'relationship_type_id' => $relationshipId,
                'is_permison_b_a' => 2
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

  static function unlink_team_lead_to_team_from_relationship(&$relationship) {

    $links = self::get_team_lead_cf_values($relationship->contact_id_a);

    // check if it exists
    foreach ($links as $key => $value) {
      // the api mixes actual values with metadata
      if (is_numeric($key)) {
        if (intval($value) == intval($relationship->contact_id_b)) {
          $params = array(
            'entity_id' => $relationship->contact_id_a,
            'custom_' . self::get_team_lead_custom_field_id() . ':' . $key => ""
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

  static function link_team_lead_to_team_from_relationship(&$relationship) {
    if(self::$adding_relationship) {
      return;
    }
    self::$adding_relationship = true;
    try {
      $links = self::get_team_lead_cf_values($relationship->contact_id_a);

      // check if it exists
      foreach ($links as $key => $value) {
        // the api mixes actual values with metadata
        if (is_numeric($key)) {
          if (intval($value) == intval($relationship->contact_id_b)) {
            return;
          }
        }
      }

      // create it
      $params = array(
        'entity_id' => $relationship->contact_id_a,
        'custom_' . self::get_team_lead_custom_field_id() => $relationship->contact_id_b
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

  private static function get_team_lead_cf_values($teamId) {

    $contactCustomFields = civicrm_api3(
      'CustomValue',
      'get',
      [
        'entity_id' => $teamId
      ]
    );

    $links = $contactCustomFields['values'][self::get_team_lead_custom_field_id()];

    return $links;
  }

  /**
   * @return mixed the id of the relationship type for team membership
   * @throws CiviCRM_API3_Exception
   */
  static function get_team_member_relationship_id() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => self::MEMBER_RELATIONSHIP_A_B
    ]);
    return $result["id"];
  }

  static function get_team_lead_relationship_id() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => self::TEAM_LEAD_RELATIONSHIP_A_B
    ]);
    return $result["id"];
  }

  static function get_team_lead_custom_field_id() {
    $result = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => ["id"],
      'custom_group_id' => "Team_Details",
      'name' => "Team_Lead",
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

  static function get_team_details_group_id() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ["id"],
      'name' => "Team_Details",
    ]);
    return $result["id"];
  }
}
?>