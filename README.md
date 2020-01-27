# Email to Post extension for phpBB

Adds a feature to receive posts via an email.

[![Build Status](https://travis-ci.com/david63/mailtopost.svg?branch=master)](https://travis-ci.com/david63/mailtopost)
[![License](https://poser.pugx.org/david63/mailtopost/license)](https://packagist.org/packages/david63/mailtopost)
[![Latest Stable Version](https://poser.pugx.org/david63/mailtopost/v/stable)](https://packagist.org/packages/david63/mailtopost)
[![Latest Unstable Version](https://poser.pugx.org/david63/mailtopost/v/unstable)](https://packagist.org/packages/david63/mailtopost)
[![Total Downloads](https://poser.pugx.org/david63/mailtopost/downloads)](https://packagist.org/packages/david63/mailtopost)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/f24d3d3994fd493d9405e6b285e46905)](https://www.codacy.com/manual/david63/mailtopost?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=david63/mailtopost&amp;utm_campaign=Badge_Grade)

## Minimum Requirements
* phpBB 3.3.0
* PHP 7.1.3

## Install
1. [Download the latest release](https://github.com/david63/mailtopost/archive/3.2.zip) and unzip it.
2. Unzip the downloaded release and copy it to the `ext` directory of your phpBB board.
3. Navigate in the ACP to `Customise -> Manage extensions`.
4. Look for `Mail to post` under the Disabled Extensions list and click its `Enable` link.

## Usage
1. Create a dedicated email address to which the emails that are to be posted on the board are to be sent.
2. Navigate in the ACP to `Extensions -> Mail to Post -> Mail to Post options`.
3. Apply the settings that you require.
4. Preferably, create a new group for those members that will be able to use this feature and put the members in that group and apply the appropriate permissions.

## Uninstall
1. Navigate in the ACP to `Customise -> Manage extensions`.
2. Click the `Disable` link for `Mail to post`.
3. To permanently uninstall, click `Delete Data`, then delete the mailtopost folder from `phpBB/ext/david63/`.

## License
[GNU General Public License v2](http://opensource.org/licenses/GPL-2.0)

© 2020 - David Wood