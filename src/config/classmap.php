<?php

/**
 * Module Class map
 *
 * Returns an associative array of class names to their corresponding
 * file paths. It is used by `Autoloader` to resolve class files manually,
 * typically for libraries, abstract class, or legacy code not using PSR-4/composer.
 *
 * @package     CiSkeleton\Modules
 * @subpackage  Pages\Config
 * @category    Autoloading
 * @author      Kader Bouyakoub <bkader[at]mail[dot]com>
 * @copyright   2018-present, Kader Bouyakoub
 * @license     MIT
 * @link        https://opensource.org/license/mit
 *
 * SPDX-License-Identifier: MIT
 */

return [
	'CI_App_pages' => __DIR__.'/../libraries/App/drivers/App_pages.php',
];
