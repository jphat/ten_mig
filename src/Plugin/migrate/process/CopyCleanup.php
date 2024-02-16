<?php

declare(strict_types=1);

namespace Drupal\ten_mig\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a ten_mig_copy_cleanup plugin that attempts to clean up text which
 * may have been copied from another source and contain unwanted characters
 * like '&nbsp;', class and id attributes, empty tags, etc. This plugin is
 * opinionated. More details on theCleanUp method below.
 *
 * Usage: include the following in your migration YAML file
 *
 * @code
 * process:
 *   bar:
 *     plugin: ten_mig_copy_cleanup
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "ten_mig_copy_cleanup")
 */
final class CopyCleanup extends ProcessPluginBase
{

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property): string
  {
    $cleaned = '';
    if ($value) {
      $cleaned = $this->theCleanUp($value, $row);
    }
    return $cleaned;
  }

  /**
   * Allowed tags are listed below; more can be added or removed as needed.
   * External HTML are opened in a new tab and have rel="noopener noreferrer"
   * The $patterns array contains regex patterns to check for. You will find
   * flagged copy in Drupal logs identified by originating node IDs.
   *
   * This plugin uses HTMLPurifier library. Config docs can be found at
   * http://htmlpurifier.org/live/configdoc/plain.html
   *
   * @param string $text
   *  The text to be cleaned up.
   * @param \Drupal\migrate\Row $row
   *  The row object.
   */
  private function theCleanUp(string $text, $row): string
  {
    $patterns = [
      'urls' => '/https:\/\/urldefense\.com\S*/i',
      'hasTable' => '/<table\S*/i',
    ];
    $types = [];

    /* @var $purifier_config \HTMLPurifier_Config */
    $config = \HTMLPurifier_Config::createDefault();
    $config->set('AutoFormat.RemoveEmpty', true);
    $config->set('AutoFormat.RemoveEmpty.RemoveNbsp', true);
    $config->set('HTML.TargetBlank', true);
    $config->set('HTML.TargetNoreferrer', true);
    $config->set('HTML.TargetNoopener', true);
    $config->set('HTML.Allowed', 'a[href|title|rel|target],b,blockquote,caption,cite,em,figcaption,figure,h2,h3,h4,h5,h6,i,img[alt|style|src|title],li,ol,p,strong,table,tbody,td,th,tr,ul,');

    $purifier = new \HTMLPurifier($config);
    $purified = $purifier->purify($text);

    // check if any of the $checklist items are in $purified if they are,
    // log a drupal message
    foreach ($patterns as $type => $pattern) {
      if (preg_match($pattern, $purified)) {
        $types[] = $type;
      }
    }

    if (!empty($types)) {
      \Drupal::logger('ten_mig')->warning('Node ID %id has a pattern violation of type(s): %types', [
        '%id' => $row->getSourceProperty('nid'),
        '%types' => implode(', ', $types),
      ]);
    }

    return $purifier->purify($text);
  }
}
