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

namespace BackOfficePath\Stack;

use BackOfficePath\BackOfficePath;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Model\ConfigQuery;

/**
 * Class BackOfficePathMiddleware
 *
 * @author Jérôme Billiras <jbilliras@openstudio.fr>
 */
class BackOfficePathMiddleware implements HttpKernelInterface
{
    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface */
    protected $app;
    
    /** @var \Symfony\Component\DependencyInjection\Container */
    protected $container;
    
    /**
     * Class constructor
     *
     * @param \Symfony\Component\HttpKernel\HttpKernelInterface $app       Kernel stack
     * @param \Symfony\Component\DependencyInjection\Container  $container Services container
     */
    public function __construct(HttpKernelInterface $app, Container $container)
    {
        $this->app = $app;
        $this->container = $container;
    }
    
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        if ($type === HttpKernelInterface::MASTER_REQUEST) {
            $prefix = ConfigQuery::read(BackOfficePath::CONFIG_PATH);
            $defaultEnabled = (int) ConfigQuery::read(BackOfficePath::CONFIG_USE_DEFAULT_PATH, 1);
            $pathInfo = $request->getPathInfo();
            
            // Discard the default /admin URL
            $discardDefaultPath =
                $defaultEnabled !== 1
                && BackOfficePath::matchPrefix($pathInfo, BackOfficePath::DEFAULT_THELIA_PREFIX)
                && $prefix !== null
                && $prefix !== ''
            ;
            
            if ($discardDefaultPath) {
                $requestStack = new RequestStack();
                $requestStack->push($request);
                $this->container->set('request_stack', $requestStack);
                
                /** @var \Thelia\Core\Template\ParserInterface $parser */
                $parser = $this->container->get('thelia.parser');
                $parser->setTemplateDefinition(
                    $this->container->get('thelia.template_helper')->getActiveFrontTemplate()
                );
                
                $this->container->get('request.context')->fromRequest($request);
                
                $response = new Response($parser->render(ConfigQuery::getPageNotFoundView()), 404);
                
                return $response;
            }
            
            if (BackOfficePath::matchPrefix($pathInfo, $prefix)
                && $prefix !== null
                && $prefix !== ''
            ) {
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
        
        return $this->app->handle($request, $type, $catch);
    }
}
