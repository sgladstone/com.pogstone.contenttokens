com.pogstone.contenttokens
==========================

This extension provides additional mail merge tokens for CiviCRM. These can be used when creating a single email, mass email or PDF letters. Click on the "insert token" link and you will see a series of additional tokens such as:

- Content of type 'article' changed in the last 7 days
- Content of type 'blog' changed in the last 4 weeks
- Content of type 'feed item' changed in the last 3 months

These tokens can be a time-saver when preparing email newsletters.  If used in combination with the core Drupal Aggregator module, then CiviCRM can be used to send email blasts basted on virtually any content source. 

The content types listed are determined based on content types in your database associated with at least 1 published node

This extension can be safely installed in CiviCRM under any CMS. However it will only add content tokens if running under Drupal 6 or 7.

This extension has been tested with Drupal 6 and Drupal 7. 

Installation Note: If your Drupal tables are in a different database than your CiviCRM tables then you will need to verify that the CiviCRM database user has permission to query tables in the Drupal database.