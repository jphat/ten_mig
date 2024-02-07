## INTRODUCTION

The ten mig module is a simple JSON-based migration module.

## REQUIREMENTS
A few modules are required for this module to work properly:
- drupal:media
- drupal:media_library
- migrate_plus:migrate_plus
- migrate_tools:migrate_tools

## INSTALLATION

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

During installation, sample files in `artifacts` directory are copied to
`public://ten_mig` directory. These files are used for migration purposes.
Upon un-installation, the `public://ten_mig` directory is removed.

## CONFIGURATION
None

## HOW TO

### General

If you want to preserve content authorship, you need to import users first (not included).

## News

News import depends on news media import, which in turn depends on news images import.
Therefore the order of import is: news images, news media, news.
`drush migrate:import ten_mig_news_images,ten_mig_news_media,ten_mig_news`
