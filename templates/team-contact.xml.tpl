<?xml version="1.0" encoding="iso-8859-1" ?>

<CustomData>
  <CustomGroups>
    <CustomGroup>
      <name>Team_Details</name>
      <title>Team Details</title>
      <extends>Organization</extends>
      <extends_entity_column_value_option_group>contact_type</extends_entity_column_value_option_group>
      <extends_entity_column_value>Team</extends_entity_column_value>
      <style>Inline</style>
      <collapse_display>1</collapse_display>
      <help_pre>&lt;p&gt;Information about a team&lt;/p&gt;</help_pre>
      <help_post></help_post>
      <weight>12</weight>
      <is_active>1</is_active>
      <table_name>civicrm_value_team_details_{$customIDs.civicrm_custom_group}</table_name>
      <is_multiple>0</is_multiple>
      <collapse_adv_display>0</collapse_adv_display>
      <created_date>2019-03-08 21:42:34</created_date>
      <is_reserved>0</is_reserved>
      <is_public>1</is_public>
    </CustomGroup>
  </CustomGroups>
  <CustomFields>
    <CustomField>
      <name>Team_Lead</name>
      <label>Team Lead</label>
      <data_type>ContactReference</data_type>
      <html_type>Autocomplete-Select</html_type>
      <is_required>0</is_required>
      <is_searchable>1</is_searchable>
      <is_search_range>0</is_search_range>
      <weight>1</weight>
      <help_pre>The team lead of the contact</help_pre>
      <is_active>1</is_active>
      <is_view>0</is_view>
      <text_length>255</text_length>
      <note_columns>60</note_columns>
      <note_rows>4</note_rows>
      <column_name>team_lead_{$customIDs.civicrm_custom_field}</column_name>
      <filter>action=get&amp;contact_sub_type=Indvidual</filter>
      <in_selector>0</in_selector>
      <custom_group_name>Team_Details</custom_group_name>
    </CustomField>
  </CustomFields>
  <ProfileGroups>
    <ProfileGroup>
      <is_active>1</is_active>
      <group_type>Team</group_type>
      <title>Team Profile</title>
      <frontend_title>Team Profile</frontend_title>
      <description>Manage a team</description>
      <add_captcha>0</add_captcha>
      <is_map>0</is_map>
      <is_edit_link>0</is_edit_link>
      <is_uf_link>0</is_uf_link>
      <is_update_dupe>0</is_update_dupe>
      <name>team_profile</name>
      <created_date>2019-03-08 21:49:15</created_date>
      <is_proximity_search>0</is_proximity_search>
      <add_cancel_button>1</add_cancel_button>
    </ProfileGroup>
  </ProfileGroups>
  <ProfileFields>
    <ProfileField>
      <field_name>legal_name</field_name>
      <is_active>1</is_active>
      <is_required>1</is_required>
      <weight>1</weight>
      <visibility>Public Pages and Listings</visibility>
      <in_selector>1</in_selector>
      <is_searchable>1</is_searchable>
      <label>Team Name</label>
      <field_type>Team</field_type>
      <profile_group_name>Team Profile</profile_group_name>
    </ProfileField>
    <ProfileField>
      <field_name>custom.civicrm_value_team_details_{$customIDs.civicrm_custom_group}.team_lead_{$customIDs.civicrm_custom_field}</field_name>
      <is_active>1</is_active>
      <weight>2</weight>
      <help_pre>The team lead of the team</help_pre>
      <visibility>Public Pages and Listings</visibility>
      <in_selector>1</in_selector>
      <is_searchable>1</is_searchable>
      <label>Team Lead</label>
      <field_type>Team</field_type>
      <profile_group_name>Team Profile</profile_group_name>
    </ProfileField>
  </ProfileFields>
</CustomData>
