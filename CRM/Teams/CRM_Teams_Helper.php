<?php
class CRM_Teams_Helper {

  /**
   * Creates a relationship when the team custom field is set
   * @param $contactId the contactId to link
   * @param $params the custom field params
   * @throws CiviCRM_API3_Exception if the api fails
   */
  static function link_contact_to_team_from_custom_field($contactId, &$params) {
    foreach ($params as $param) {
      if($param["column_name"] == "individual_to_team" && $param['value'] != null) {
        $teamId = $param['value'];
        $relationshipId = self::get_team_member_relationship_id();
        // check the relationship doesn't already exist
        $result = civicrm_api3('Relationship', 'get', [
          'sequential' => 1,
          'contact_id_a' => $teamId,
          'contact_id_b' => $contactId,
          'relationship_type_id' => $relationshipId,
        ]);
        if($result['count'] == 0) {
          $input = array(
            'contact_id_a' => intval($teamId),
            'contact_id_b' => intval($contactId),
            'relationship_type_id' => $relationshipId
          );
          $otherinput['test'] = 1;
          // create it
          $result = civicrm_api3('Relationship', 'create', $input);
        } else {
          //TODO: work out if doing nothing is the correct behaviour
        }
      }
    }
  }

  static function link_contact_to_team_from_relationship(&$relationship) {
    $contactCustomFields = civicrm_api3(
      'CustomValue',
      'get'.
      [
        'entity_id' => $relationship->contact_id_b
      ]
    );


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
}
?>