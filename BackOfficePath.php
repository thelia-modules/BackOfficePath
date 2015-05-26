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
 */
class BackOfficePath extends BaseModule
{
    /**
     * @var string Translation domain
     */
    const MESSAGE_DOMAIN = 'backofficepath';

    /**
     * @var string Default admin path
     */
    const DEFAULT_THELIA_PREFIX = 'admin';

    /**
     * @var string Configuration key for new path
     */
    const CONFIG_PATH = 'back_office_path';

    /**
     * @var string Configuration key for using default path
     */
    const CONFIG_USE_DEFAULT_PATH = 'back_office_path_default_enabled';

    public static function getModuleCode()
    {
        $fullClassName = explode('\\', get_called_class());

        return end($fullClassName);
    }

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
}
