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



  // Civi uses differing data structures for different form submissions
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

      $contactID = (int)$form->_contactId ?: 0;

      foreach ($fields['email'] as $emailKey => $emailData) {
        $email = $emailData['email'];
        if (!empty($email)) {
          if (duplicate_emails_email_is_used($email, $contactID)) {
            $errors["email[{$emailKey}][email]"] = ts("This email address is unavailable.");
          }
        }
      }
      break;
    
    // Form added to Drupal user entity form
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
        if (!empty($email) && duplicate_emails_email_is_used($email, $contactID)) {
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

  $emails = \Civi\Api4\Email::get()
    ->addWhere("contact_id", "!=", $contactID)
    ->addWhere("email", "=", $email)
    ->addWhere("contact.is_deleted", "=", 0)
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
