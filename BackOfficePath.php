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
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
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

    public function preActivation(?ConnectionInterface $con = null): bool
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
    public static function replaceUrl(string $content, string $oldPrefix, string $newPrefix): string
    {
        // Skip paths that already carry the new prefix. When the custom prefix
        // starts with the default one (e.g. "admin" -> "admin_xxx"), a naive
        // replace turns "/admin_xxx" into "/admin_xxx_xxx" on every pass, so the
        // value kept growing each time a back-office page was re-rendered/saved.
        // The negative lookahead makes the replacement idempotent.
        $suffix = str_starts_with($newPrefix, $oldPrefix)
            ? substr($newPrefix, \strlen($oldPrefix))
            : '';

        $guard = $suffix !== '' ? '(?!' . preg_quote($suffix, '#') . ')' : '';

        // Only replace the prefix as a whole path segment: "/admin" inside
        // "/administrators" must be left alone, otherwise links such as
        // "/admin/configuration/administrators" end up pointing to a
        // non-existent "/admin_xxx/configuration/admin_xxxistrators" route.
        return preg_replace(
            '#/' . preg_quote($oldPrefix, '#') . $guard . '(?![\w-])#',
            '/' . $newPrefix,
            $content
        ) ?? '';
    }

    public static function matchPath(string $path, string $prefix): bool
    {
        return preg_match("/^\/".preg_quote($prefix, '/')."(\/.*$|$)/", $path) === 1;
    }

    public static function matchUrl(string $path, string $prefix): bool
    {
        return preg_match("/\/".preg_quote($prefix, '/')."(\/.*$|$)/", $path) === 1;
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([__DIR__.'/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
