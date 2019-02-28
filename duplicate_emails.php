<?php

require_once 'duplicate_emails.civix.php';
use CRM_DuplicateEmails_ExtensionUtil as E;

/**
 * Implements hook_civicrm_validateForm().
 * 
 * Prevents email addresses being used across multiple contacts
 *
 * @param string $formName
 * @param array $fields
 * @param array $files
 * @param CRM_Core_Form $form
 * @param array $errors
 * 
 * @return
 */
function duplicate_emails_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) : void {

  $email = null;
  $contactID = null;
  switch ($formName) {

    // Profile preview
    case 'CRM_Contact_Form_Inline_Email':

      $contactID = (int)$fields['cid'];
      foreach ($fields['email'] as $emailKey => $emailData) {
        $email = $emailData['email'];
        if (!empty($email)) {
          if (duplicate_emails_email_is_used($email, $contactID)) {
            $errors["email[{$emailKey}][email]"] = ts("This email address is unavailable.");
          }
        }
      }
      break;

    // Full contact edit form
    case 'CRM_Contact_Form_Contact':

      $contactID = (int)$form->_contactId;
      foreach ($fields['email'] as $emailKey => $emailData) {
        $email = $emailData['email'];
        if (!empty($email)) {
          if (duplicate_emails_email_is_used($email, $contactID)) {
            $errors["email[{$emailKey}][email]"] = ts("This email address is unavailable.");
          }
        }
      }
      break;
    
    // Form added to Drupal user entity
    case 'CRM_Profile_Form_Dynamic':
    
      $contactID = (int)$form->get('id');
      // Define an array of fields to validate
      $emailsToValidate = [
        'email-Primary',
        'email-1',
        'email-2',
        'email-3',
        'email-4'
      ];

      foreach ($emailsToValidate as $emailKey) {
        $email = $fields[$emailKey];
        if (duplicate_emails_email_is_used($email, $contactID)) {
          $errors[$emailKey] = ts("This email address is unavailable. Please <a href='/contact'>contact CEPR</a> if you require assistance.");
        }
      }
      break;
  }

  return;
}

/**
 * Given an email and contact ID, this function will check the
 * civicrm_emails table for addresses that match $email, but not
 * $contactID I.e. an account is already using that email address
 * 
 * @param string $email     The email to lookup
 * @param int    $contactID The contact ID not to include
 * 
 * @return bool  True if the email has already been used on another account
 *               False if the email is not a duplicate
 */
function duplicate_emails_email_is_used(string $email, int $contactID) : bool {
  
  $perms = \Civi\Api4\Email::permissions();

  $emails = \Civi\Api4\Email::get()
    ->addWhere("contact_id", "!=", $contactID)
    ->addWhere("email", "=", $email)
    ->setCheckPermissions(false)
    ->setLimit(1)
    ->execute();
  
  $emailExists = $emails->first();
  if ($emailExists) {
    // This email exists on another contact
    return true;
  }

  // No other contacts with this email exist
  return false;
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function duplicate_emails_civicrm_config(&$config) {
  _duplicate_emails_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function duplicate_emails_civicrm_xmlMenu(&$files) {
  _duplicate_emails_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function duplicate_emails_civicrm_install() {
  _duplicate_emails_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function duplicate_emails_civicrm_postInstall() {
  _duplicate_emails_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function duplicate_emails_civicrm_uninstall() {
  _duplicate_emails_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function duplicate_emails_civicrm_enable() {
  _duplicate_emails_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function duplicate_emails_civicrm_disable() {
  _duplicate_emails_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function duplicate_emails_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _duplicate_emails_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function duplicate_emails_civicrm_managed(&$entities) {
  _duplicate_emails_civix_civicrm_managed($entities);
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
function duplicate_emails_civicrm_caseTypes(&$caseTypes) {
  _duplicate_emails_civix_civicrm_caseTypes($caseTypes);
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
function duplicate_emails_civicrm_angularModules(&$angularModules) {
  _duplicate_emails_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function duplicate_emails_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _duplicate_emails_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function duplicate_emails_civicrm_entityTypes(&$entityTypes) {
  _duplicate_emails_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function duplicate_emails_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 *
function duplicate_emails_civicrm_navigationMenu(&$menu) {
  _duplicate_emails_civix_insert_navigation_menu($menu, 'Mailings', array(
    'label' => E::ts('New subliminal message'),
    'name' => 'mailing_subliminal_message',
    'url' => 'civicrm/mailing/subliminal',
    'permission' => 'access CiviMail',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _duplicate_emails_civix_navigationMenu($menu);
} // */
