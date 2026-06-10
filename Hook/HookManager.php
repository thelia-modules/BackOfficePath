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

namespace BackOfficePath\Hook;

use BackOfficePath\Form\Configuration as ConfigurationForm;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\ConfigQuery;

class HookManager extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public function onModuleConfigure(HookRenderEvent $event): void
    {
        $form = $this->formFactory->createForm(ConfigurationForm::getName(), data: [
            'back_office_path' => ConfigQuery::read('back_office_path', ''),
            'back_office_path_default_enabled' => (bool) (ConfigQuery::read('back_office_path_default_enabled', '0') === '1'),
        ]);

        $event->add(
            $this->render('BackOfficePath/module_configuration.html.twig', [
                'form' => $form->createView()->getView(),
            ])
        );
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                [
                    'type' => 'back',
                    'method' => 'onModuleConfigure',
                ],
            ],
        ];
    }
}
