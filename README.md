com.pogstone.contenttokens
==========================

This extension provides additional mail merge tokens for CiviCRM. These can be used when creating a single email, mass email or PDF letters. Click on the "insert token" link and you will see a series of additional tokens such as:

- Content of type 'article' created in the last 7 days
- Content of type 'blog' created in the last 4 weeks
- Content of type 'feed item' created in the last 3 months
- Content of type 'featured' (Joomla specific)

- Content with category 'beaches' created in the last 7 days
- Content with category 'mountains' created in the last 4 weeks
- Content with category 'forests' created in the last 3 months

- Content from feed 'Interesting blog' created in the last 7 days
- Content from feed 'Interesting blog' created in the last 4 weeks
- Content from feed 'Interesting blog' created in the last 3 months

These tokens can be a time-saver when preparing email newsletters.  If used in combination with a CMS aggregator (such as the core Drupal Aggregator module) then CiviCRM can be used to send email blasts basted on virtually any content source, local or external. 

The content types listed are determined based on content types in your database associated with at least 1 published content item.

The categories listed are determined based on category terms in your database associated with at least 1 published content item. (ie Drupal taxonomy terms or WordPress terms or Joomla categories)

The feeds listed are determined based on feeds configured within the core Drupal Aggregator module. (Hopefully some WordPress or Joomla folks will help to get feeds working for their CMS)

This extension can be safely installed in CiviCRM under any CMS.

This extension has been tested with:
 Drupal 6 and Drupal 7
 Joomla 3

 Limited testing has been done with WordPress. (Be sure to submit your code changes on GitHub at: https://github.com/sgladstone/com.pogstone.contenttokens ) 

Installation Note: If your CMS tables are in a different database than your CiviCRM tables then you will need to verify that the CiviCRM database user has permission to query tables in the CMS database.