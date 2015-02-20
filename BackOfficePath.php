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

class BackOfficePath extends BaseModule
{

    const MESSAGE_DOMAIN = "backofficepath";

    const DEFAULT_THELIA_PREFIX = "admin";

    /**
     * Backward compatibility
     * @return string The module code, in a static wayord
     */
    public static function getModuleCode()
    {
        $fullClassName = explode('\\', get_called_class());
        return end($fullClassName);
    }

    public function preActivation(ConnectionInterface $con = null)
    {
        $prefix = ConfigQuery::read("back_office_path");

        if (null === $prefix){
            ConfigQuery::write("back_office_path", '', false, true);
        }

        $enabled = ConfigQuery::read("back_office_path_default_enabled", null);

        if (null === $enabled){
            ConfigQuery::write("back_office_path_default_enabled", '1', false, true);
        }

        return true;
    }

}
