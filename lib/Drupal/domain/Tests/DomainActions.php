<?php

/**
 * @file
 * Definition of Drupal\domain\Tests\DomainActions.
 */

namespace Drupal\domain\Tests;
use Drupal\domain\DomainInterface;

/**
 * Tests the domain record actions.
 */
class DomainActions extends DomainTestBase {

  public static function getInfo() {
    return array(
      'name' => 'Domain record actions',
      'description' => 'Tests domain record actions.',
      'group' => 'Domain',
    );
  }

  /**
   * Test bulk actions through the Views module.
   */
  function testDomainActions() {
    $this->admin_user = $this->drupalCreateUser(array('administer domains', 'access administration pages'));
    $this->drupalLogin($this->admin_user);

    $path = 'admin/structure/domain';

    // No domains should exist.
    $this->domainTableIsEmpty();

    // Create test domains.
    $this->domainCreateTestDomains(4);

    // Visit the domains views administration page.
    $this->drupalGet($path);
    $this->assertResponse(200);

    // Test the domains.
    $domains = domain_load_multiple(NULL, TRUE);
    $this->assertTrue(count($domains) == 4, 'Four domain records found.');

    // Check the default domain.
    $default = domain_default_id();
    $this->assertTrue($default == 1, 'Default domain set correctly.');

    // Test some text on the page.
    foreach ($domains as $domain) {
      $this->assertText($domain->name, format_string('@name found on views page.', array('@name' => $domain->name)));
      $this->assertText($domain->machine_name, format_string('@machine_name found on views page.', array('@machine_name' => $domain->machine_name)));
    }
    // @TODO: Test the list of actions.
    $actions = array('domain_delete_action', 'domain_enable_action', 'domain_disable_action', 'domain_default_action');
    foreach ($actions as $action) {
      $this->assertRaw("<option value=\"{$action}\">", format_string('@action action found.', array('@action' => $action)));
    }

    // Testing domain_delete_action.
    $edit = array(
      'action_bulk_form[1]' => TRUE,
      'action' => 'domain_delete_action',
    );

    $this->drupalPostForm($path, $edit, t('Apply'));
    $this->assertText('Delete domain record was applied to 1 item.');

    // Check that one domain was removed.
    $domains = domain_load_multiple(NULL, TRUE);
    $this->assertTrue(count($domains) == 3, 'One domain deleted.');

    // Testing domain_default_action.
    $edit = array(
      'action_bulk_form[1]' => TRUE,
      'action' => 'domain_default_action',
    );
    $this->drupalPostForm($path, $edit, t('Apply'));
    $this->assertText('Set default domain record was applied to 1 item.');

    // Test the default domain, which should now be id 3.
    $default = domain_default_id();
    $this->assertTrue($default == 3, 'Default domain set correctly.');

    // Testing domain_disable_action.
    $edit = array(
      'action_bulk_form[1]' => TRUE,
      'action_bulk_form[2]' => TRUE,
      'action' => 'domain_disable_action',
    );
    $this->drupalPostForm($path, $edit, t('Apply'));
    $this->assertText('The default domain cannot be disabled.');
    $this->assertText('Disable domain record was applied to 2 items.');

    // @TODO: Test the count of disabled domains.

    // Testing domain_enable_action.
    $edit = array(
      'action_bulk_form[2]' => TRUE,
      'action' => 'domain_enable_action',
    );
    $this->drupalPostForm($path, $edit, t('Apply'));
    $this->assertText('Enable domain record was applied to 1 item.');

    // @TODO: Test the count of disabled domains.

  }

}

