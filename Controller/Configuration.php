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

namespace BackOfficePath\Controller;

use BackOfficePath\BackOfficePath;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Template\ParserContext;
use Thelia\Model\ConfigQuery;

/**
 * Class Configuration
 * @package BackOfficePath\Controller
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
#[Route('/admin/module/BackOfficePath', name: 'backofficepath_configuration')]
class Configuration extends BaseAdminController
{
    #[Route('/save', name: '_save', methods: ['POST'])]
    public function saveAction(ParserContext $parserContext): RedirectResponse|Response
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['backofficepath'], AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(\BackOfficePath\Form\Configuration::getName());
        $message = '';

        try {
            $vform = $this->validateForm($form);
            $data = $vform->getData();

            ConfigQuery::write('back_office_path', trim($data['back_office_path'], '/'), false, true);
            ConfigQuery::write(
                'back_office_path_default_enabled',
                $data['back_office_path_default_enabled'] ? '1' : '0',
                false,
                true
            );
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        if ($message) {
            $form->setErrorMessage($message);
            $parserContext
                ->addForm($form)
                ->setGeneralError($message);
        }

        return $this->generateRedirectFromRoute('admin.module.configure', [], ['module_code' => BackOfficePath::getModuleCode()]);
    }
}
