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
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\ConfigQuery;
use Thelia\Tools\URL;

/**
 * Class Configuration
 * @package HookSocial\Controller
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class Configuration extends BaseAdminController
{
    public function saveAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['backofficepath'], AccessManager::UPDATE)) {
            return $response;
        }

        $form = new \BackOfficePath\Form\Configuration($this->getRequest());
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
            $this->getParserContext()->addForm($form);
            $this->getParserContext()->setGeneralError($message);
        }
    
        return $this->generateRedirect(URL::getInstance()->absoluteUrl('/admin/module/' . BackOfficePath::getModuleCode()));
    }
}
