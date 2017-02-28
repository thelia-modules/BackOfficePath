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

namespace BackOfficePath\Form;

use BackOfficePath\BackOfficePath;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Thelia\Model\ConfigQuery;

/**
 * Class Configuration
 *
 * @author Julien Chanséaume <jchanseaume@openstudio.fr>
 */
class Configuration extends BaseForm
{
    
    const PREFIXE_PATTERN = '^[A-Za-z0-9\-_]*$';
    
    protected function buildForm()
    {
        $form = $this->formBuilder;
        
        $form
            ->add(
                'back_office_path',
                'text',
                array(
                    'constraints' => array(
                        new NotBlank(),
                        new Regex([
                            'pattern' => '#' . self::PREFIXE_PATTERN . '#',
                            'message' => Translator::getInstance()->trans(
                                'URL should only use alpha numeric, - and _ characters',
                                [],
                                BackOfficePath::MESSAGE_DOMAIN
                            )
                        ])
                    ),
                    'data' => ConfigQuery::read('back_office_path', ''),
                    'label' => Translator::getInstance()->trans(
                        'The new prefix',
                        [],
                        BackOfficePath::MESSAGE_DOMAIN
                    ),
                    'label_attr' => array(
                        'for' => 'back_office_path',
                        'help' => Translator::getInstance()->trans(
                            'It will replaced the default <code>%prefix</code>',
                            ['%prefix' => '/' . BackOfficePath::DEFAULT_THELIA_PREFIX],
                            BackOfficePath::MESSAGE_DOMAIN
                        )
                    ),
                )
            )
            ->add(
                'back_office_path_default_enabled',
                'checkbox',
                array(
                    'data' => intval(ConfigQuery::read('back_office_path_default_enabled', '')) === 1,
                    'label' => Translator::getInstance()->trans(
                        'Use also the default prefix',
                        [],
                        BackOfficePath::MESSAGE_DOMAIN
                    ),
                    'label_attr' => array(
                        'for' => 'back_office_path_default_enabled',
                        'help' => Translator::getInstance()->trans(
                            'Activate this to test your new prefix. If it doesn\'t work you could rollback your changes (the link in the HTML content will not be replaced)',
                            [],
                            BackOfficePath::MESSAGE_DOMAIN
                        )
                    ),
                )
            );
    }
    
    /**
     * @return string the name of you form. This name must be unique
     */
    public function getName()
    {
        return 'backofficepath';
    }
}
