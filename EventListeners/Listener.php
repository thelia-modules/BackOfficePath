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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Thelia\Model\ConfigQuery;

/**
 * Class ResponseListener
 *
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 */
class Listener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [
                ['doResponse', 10]
            ]
        ];
    }

    /**
     * Handle response on KernelEvents::RESPONSE
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event A FilterResponseEvent object
     */
    public function doResponse(FilterResponseEvent $event)
    {
        // Skip if the default thelia prefix is used
        if (!$event->getRequest()->attributes->get(BackOfficePath::IS_CUSTOM_ADMIN_PATH)) {
            return;
        }

        // Replace default admin url by custom one
        $prefix = ConfigQuery::read(BackOfficePath::CONFIG_PATH);
        
        if (! BackOfficePath::matchPrefix($event->getRequest()->getPathInfo(), BackOfficePath::DEFAULT_THELIA_PREFIX)
            && $prefix !== null
            && $prefix !== ''
        ) {
            $response = $event->getResponse();

            if ($response instanceof RedirectResponse) {
                $targetUrl = $response->getTargetUrl();
                
                if (BackOfficePath::matchPrefix($targetUrl, BackOfficePath::DEFAULT_THELIA_PREFIX)) {
                    $newUrl = BackOfficePath::replaceUrl($targetUrl, BackOfficePath::DEFAULT_THELIA_PREFIX, $prefix);
                    $response->setTargetUrl($newUrl);
                }
            } else {
                $content = BackOfficePath::replaceUrl(
                    $response->getContent(),
                    BackOfficePath::DEFAULT_THELIA_PREFIX,
                    $prefix
                );
                
                $response->setContent($content);
            }
        }
    }
}
