<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace BackOfficePath;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Model\ConfigQuery;
use Thelia\Module\BaseModule;

/**
 * Class BackOfficePath
 *
 * @author Florian Picard <fpicard@openstudio.fr>
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 */
class BackOfficePath extends BaseModule
{
    /** @var string Translation domain  */
    const MESSAGE_DOMAIN = 'backofficepath';

    /** @var string Default admin path */
    const DEFAULT_THELIA_PREFIX = 'admin';

    /** @var string Configuration key for new path  */
    const CONFIG_PATH = 'back_office_path';

    /** @var string Configuration key for using default path */
    const CONFIG_USE_DEFAULT_PATH = 'back_office_path_default_enabled';

    /** @var string Request attribute key to determine if custom admin path is in use */
    const IS_CUSTOM_ADMIN_PATH = 'is_custom_admin_path';

    public function preActivation(ConnectionInterface $con = null)
    {
        $prefix = ConfigQuery::read(self::CONFIG_PATH);
        if ($prefix === null) {
            ConfigQuery::write(self::CONFIG_PATH, '', false, true);
        }

        $enabled = ConfigQuery::read(self::CONFIG_USE_DEFAULT_PATH, null);

        if ($enabled === null) {
            ConfigQuery::write(self::CONFIG_USE_DEFAULT_PATH, true, false, true);
        }

        return true;
    }

    /**
     * Replace url in content
     *
     * @param string $content
     * @param string $oldPrefix
     * @param string $newPrefix
     *
     * @return string Content with replaced urls
     */
    public static function replaceUrl($content, $oldPrefix, $newPrefix)
    {
        $replacedUrl = preg_replace(
            '#(.*?)/' . preg_quote($oldPrefix, '#') . '(.*?)#',
            '$1/' . $newPrefix . '$2',
            $content
        );
    
        return $replacedUrl;
    }
    
    public static function matchPath($path, $prefix)
    {
        return preg_match("/^\/".preg_quote($prefix, '/')."(\/.*$|$)/", $path) === 1;
    }
    
    public static function matchUrl($path, $prefix)
    {
        return preg_match("/\/".preg_quote($prefix, '/')."(\/.*$|$)/", $path) === 1;
    }
}
