<?php
/**
 * Replaces the string order reference with numeric one
 *
 * @package   gmnumeric
 * @author    Dariusz Tryba
 * @copyright Copyright (c) Green Mouse Studio (http://www.greenmousestudio.com)
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Gmnumeric extends Module
{

    public function __construct()
    {
        $this->name = 'gmnumeric';
        $this->tab = 'administration';
        $this->version = '1.2.0';
        $this->author = 'GreenMouseStudio.com';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Numeric Order Reference');
        $this->description = $this->l('Changes order reference from string to numeric');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this amazing module?');
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return parent::install() &&
            Configuration::updateValue('GMNUMERIC_RANDOM', true) &&
            Configuration::updateValue('GMNUMERIC_RANDOM', true) &&
            Configuration::updateValue('GMNUMERIC_PREFIX', '');
    }

    public function uninstall()
    {
        Configuration::deleteByName('GMNUMERIC_RANDOM');
        Configuration::deleteByName('GMNUMERIC_ZEROS');
        Configuration::deleteByName('GMNUMERIC_PREFIX');
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->postProcess();
            $output .= $this->displayConfirmation($this->l('Settings updated'));
        }
        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('gmnumeric_preview', Order::generateReference());
        return $output.$this->displayForm()
            .$this->context->smarty->fetch($this->local_path.'views/templates/admin/gmnumeric.tpl')
            .$this->context->smarty->fetch($this->local_path.'views/templates/admin/gms.tpl');
    }

    public function displayForm()
    {
        $options = array(
            array(
                'id_option' => 'ZEROS',
                'name' => $this->l('Add leading zeros (to order ID)')
            ),
        );
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'radio',
                        'label' => $this->l('Reference format'),
                        'name' => 'GMNUMERIC_RANDOM',
                        'class' => 't',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Random number')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Order ID')
                            )
                        ),
                    ),
                    array(
                        'type' => 'checkbox',
                        'name' => 'GMNUMERIC',
                        'class' => 't',
                        'is_bool' => true,
                        'values' => array(
                            'query' => $options,
                            'id' => 'id_option',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('Prefix'),
                        'class' => 't',
                        'name' => 'GMNUMERIC_PREFIX',
                        'desc' => $this->l('3 characters max')
                    )
                ),
                'submit' => array(
                    'title' => $this->l('Save')
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->table = $this->table;
        $lang = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->module = $this;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG')
                : 0;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit'.$this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->fields_value['GMNUMERIC_RANDOM'] = Configuration::get('GMNUMERIC_RANDOM');
        $helper->fields_value['GMNUMERIC_ZEROS'] = Configuration::get('GMNUMERIC_ZEROS');
        $helper->fields_value['GMNUMERIC_PREFIX'] = Configuration::get('GMNUMERIC_PREFIX');
        return $helper->generateForm(array($fields_form));
    }

    protected function postProcess()
    {
        $random = Tools::getValue('GMNUMERIC_RANDOM');
        Configuration::updateValue('GMNUMERIC_RANDOM', $random);
        $zeros = Tools::getValue('GMNUMERIC_ZEROS');
        Configuration::updateValue('GMNUMERIC_ZEROS', $zeros);
        $prefix = substr(Tools::getValue('GMNUMERIC_PREFIX'), 0, 3);
        Configuration::updateValue('GMNUMERIC_PREFIX', $prefix);
    }
}
