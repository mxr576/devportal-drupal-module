#!/usr/bin/env bash
# Based on https://github.com/apigee/apigee-edge-drupal/blob/8.x-1.0/.travis/prepare-test-env.sh.

set -e

# Make sure that script is standalone (it can be used even if it is not called
# by run-test.sh).
MODULE_PATH=/opt/drupal-module
WEB_ROOT=${WEB_ROOT:-"/var/www/html/web"}
WEB_ROOT_PARENT=${WEB_ROOT_PARENT:-"/var/www/html"}
TEST_ROOT=${TEST_ROOT:-"modules/drupal-module}"}
TESTRUNNER=${TESTRUNNER:-"/var/www/html/testrunner"}

COMPOSER_GLOBAL_OPTIONS="--no-interaction --no-suggest -o"

# We mounted the cache/files folder from the host so we have to fix permissions
# on the parent cache folder because it did not exist before.
sudo -u root sh -c "chown -R wodby:wodby /home/wodby/.composer/cache"

sudo -u root sh -c "chown -R wodby:wodby /opt/drupal-module"

cd ${MODULE_PATH}/.travis

if [[ -n "${INSTALL_COMPOSER_VERSION}" ]]; then
  wget https://github.com/composer/composer/releases/download/${INSTALL_COMPOSER_VERSION}/composer.phar -O /tmp/composer \
  && chmod u+x /tmp/composer \
  && sudo -u root mv /tmp/composer /usr/local/bin/composer
fi

composer --version

# Install module with the highest dependencies first.
composer update -vvv --profile ${COMPOSER_GLOBAL_OPTIONS}

# Allow to run tests with a specific Drupal core version (ex.: latest dev).
if [[ -n "${DRUPAL_CORE}" ]]; then
  composer require drupal/core:${DRUPAL_CORE} webflo/drupal-core-require-dev:${DRUPAL_CORE} ${COMPOSER_GLOBAL_OPTIONS};
fi

# Downgrade dependencies if needed.
if [[ -n "${DEPENDENCIES}" ]]; then
  composer update ${COMPOSER_GLOBAL_OPTIONS} ${DEPENDENCIES} --with-dependencies
fi

rm -f composer.lock

cp settings.testing.php ${WEB_ROOT}/sites/default

# Symlink module to the contrib folder.
ln -s ${MODULE_PATH} ${WEB_ROOT}/modules/drupal-module

# Pre-create simpletest and screenshots directories...
sudo -u root -E mkdir -p ${WEB_ROOT}/sites/simpletest
sudo -u root mkdir -p /mnt/files/log/screenshots

# Based on https://www.drupal.org/node/244924, but we had to grant read
# access to files and read + execute access to directories to "others"
# otherwise Javascript tests fails.
# (Error: jQuery was not found an AJAX form.)
sudo -u root -E sh -c "chown -R wodby:www-data $WEB_ROOT \
    && find $WEB_ROOT -type d -exec chmod 6755 '{}' \; \
    && find $WEB_ROOT -type f -exec chmod 0644 '{}' \;"

sudo -u root -E sh -c "mkdir -p $WEB_ROOT/sites/default/files \
    && chown -R wodby:www-data $WEB_ROOT/sites/default/files \
    && chmod 6770 $WEB_ROOT/sites/default/files"

# Make sure that the log folder is writable for both www-data and wodby users.
# Also create a dedicated folder for PHPUnit outputs.
sudo -u root sh -c "chown -R www-data:wodby /mnt/files/log \
 && chmod -R 6750 /mnt/files/log \
 && mkdir -p /mnt/files/log/simpletest/browser_output \
 && chown -R www-data:wodby /mnt/files/log/simpletest \
 && chmod -R 6750 /mnt/files/log/simpletest \
 && chown -R www-data:wodby /mnt/files/log/screenshots \
 && chmod -R 6750 /mnt/files/log/screenshots"

# Change location of the browser_output folder, because it seems even if
# BROWSERTEST_OUTPUT_DIRECTORY is set the html output is printed out to
# https://github.com/drupal/core/blob/8.7.3/tests/Drupal/Tests/BrowserHtmlDebugTrait.php#L121
sudo -u root ln -s /mnt/files/log/simpletest/browser_output ${WEB_ROOT}/sites/simpletest/browser_output

# Fix permissions on on simpletest and its sub-folders.
sudo -u root sh -c "chown -R www-data:wodby $WEB_ROOT/sites/simpletest \
    && chmod -R 6750 $WEB_ROOT/sites/simpletest"

# Let's display installed dependencies and their versions.
composer show -f json

# Downloading the test runner.
curl -s -L -o ${TESTRUNNER} https://github.com/Pronovix/testrunner/releases/download/v0.4/testrunner-linux-amd64
chmod +x ${TESTRUNNER}
