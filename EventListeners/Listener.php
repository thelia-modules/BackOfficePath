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
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception;

use Thelia\Model\ConfigQuery;

/**
 * Class ResponseListener
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class Listener implements EventSubscriberInterface
{

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['doResponse', 10],
            KernelEvents::REQUEST  => ['doRequest', 255]
        ];
    }

    public function doResponse(FilterResponseEvent $event)
    {
        $response       = $event->getResponse();
        $url            = $event->getRequest()->getPathInfo();
        $prefix         = ConfigQuery::read("back_office_path");
        $defaultEnabled = intval(ConfigQuery::read("back_office_path_default_enabled", "1"));
        $contentType    = $response->headers->get('Content-Type');

        // skip if the default thelia prefixe is enabled
        if ($defaultEnabled === 1) {
            return;
        }

        $isValid = strpos($url, '/' . BackOfficePath::DEFAULT_THELIA_PREFIX) === 0 &&
            $prefix !== null && $prefix !== ""
            // && (null === $contentType || false !== strpos($contentType, "text/html"))
        ;

        if ($isValid) {
            if ($response instanceof RedirectResponse) {
                $targetUrl = $response->getTargetUrl();
                if (strpos($targetUrl, '/' . BackOfficePath::DEFAULT_THELIA_PREFIX) !== false) {
                    $newUrl = $this->replaceUrl($targetUrl, BackOfficePath::DEFAULT_THELIA_PREFIX, $prefix);
                    $response->setTargetUrl($newUrl);
                }
            } else {
                $content = $this->replaceUrl(
                    $response->getContent(),
                    BackOfficePath::DEFAULT_THELIA_PREFIX,
                    $prefix
                );
                $response->setContent($content);
            }
        }

    }

    protected function replaceUrl($content, $oldPrefixe, $newPrefixe)
    {
        $replacedUrl = preg_replace(
            '|/' . preg_quote($oldPrefixe) . '([/\?#"\'])?|',
            '/' . $newPrefixe . '$1',
            $content
        );

        return $replacedUrl;
    }

    public function doRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $prefix         = ConfigQuery::read("back_office_path");
        $defaultEnabled = intval(ConfigQuery::read("back_office_path_default_enabled", "1"));
        $pathInfo       = $event->getRequest()->getPathInfo();
        $url            = $event->getRequest()->server->get('REQUEST_URI');


        // Discard the default /admin URL
        $isValid = 1 !== $defaultEnabled &&
            $pathInfo === '/' . BackOfficePath::DEFAULT_THELIA_PREFIX &&
            $prefix !== null && $prefix !== ""
        ;

        if ($isValid) {
            throw new NotFoundHttpException();
        }

        // Check if the URL is an backOffice URL
        $isValid = strpos($pathInfo, '/' . $prefix) === 0 &&
            $prefix !== null && $prefix !== ""
        ;

        if ($isValid) {

            $newUrl = $this->replaceUrl($url, $prefix, BackOfficePath::DEFAULT_THELIA_PREFIX);
            $event->getRequest()->server->set('REQUEST_URI', $newUrl);

            $event->getRequest()->initialize(
                $event->getRequest()->query->all(),
                $event->getRequest()->request->all(),
                $event->getRequest()->attributes->all(),
                $event->getRequest()->cookies->all(),
                $event->getRequest()->files->all(),
                $event->getRequest()->server->all(),
                $event->getRequest()->getContent()
            );
        }

    }

    protected function swapUrl($url, $oldPrefix, $newPrefix)
    {
        return sprintf(
            "%s%s%s",
            '/',
            $newPrefix,
            substr($url, 1 + strlen($oldPrefix))
        );
    }
}
