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

namespace BackOfficePath\EventListeners;

use BackOfficePath\BackOfficePath;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Core\Template\ParserInterface;
use Thelia\Model\ConfigQuery;

/**
 * Class BackOfficePathMiddleware
 *
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 */
class BackOfficePathKernelListener implements EventSubscriberInterface

{
    protected ContainerInterface $container;
    protected RequestStack $requestStack;

    /**
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['processBackOfficeUrl', \PHP_INT_MAX - 2]
            ],
        ];
    }

    /**
     * @param RequestEvent $event
     * @return void
     * @throws \SmartyException
     */
    public function processBackOfficeUrl(RequestEvent $event)
    {
        if ($event->getRequestType() !== HttpKernelInterface::MAIN_REQUEST) {
            return;
        }

        $request = $event->getRequest();

        $prefix = ConfigQuery::read(BackOfficePath::CONFIG_PATH);
        $defaultEnabled = (int) ConfigQuery::read(BackOfficePath::CONFIG_USE_DEFAULT_PATH, 1);
        $pathInfo = $request->getPathInfo();

        // Discard the default /admin URL
        $discardDefaultPath =
            $defaultEnabled !== 1
            && $prefix !== null
            && $prefix !== ''
            && BackOfficePath::matchPath($pathInfo, BackOfficePath::DEFAULT_THELIA_PREFIX)
        ;

        if ($discardDefaultPath) {
            throw new NotFoundHttpException();
        }

        if ($prefix !== null && $prefix !== '' && BackOfficePath::matchPath($pathInfo, $prefix)) {
            $customAdminPath = BackOfficePath::replaceUrl(
                $request->getRequestUri(),
                $prefix,
                BackOfficePath::DEFAULT_THELIA_PREFIX
            );

            $request->server->set('REQUEST_URI', $customAdminPath);
            $request->attributes->set(BackOfficePath::IS_CUSTOM_ADMIN_PATH, true);

            $request->initialize(
                $request->query->all(),
                $request->request->all(),
                $request->attributes->all(),
                $request->cookies->all(),
                $request->files->all(),
                $request->server->all(),
                $request->getContent()
            );
        }
    }
}
