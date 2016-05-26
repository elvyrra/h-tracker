<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\HTracker;

/**
 * This class describes the behavio of the installer for the plugin {$data['name']}
 */
class Installer extends PluginInstaller {
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install() {
        // Create the table for the projects
        Project::createTable();

        // Create the table for the tickets
        Ticket::createTable();

        // Create the table for the tickets comments
        TicketComment::createTable();

        Option::set($this->_plugin.'.status',
            json_encode(
                array(
                    array(
                        'id'    => 1,
                        'order' => 0,
                        'label' => 'open',
                    ),
                    array(
                        'id'    => 2,
                        'order' => 1,
                        'label' => 'closed',
                    ),
                )
            )
        );

    }


    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall() {
        // Remove table from database
        TicketComment::dropTable();

        Ticket::dropTable();

        Project::dropTable();
    }


    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate() {
        $permission = Permission::add($this->_plugin.'.manage-ticket', 0, 0);

        $menu = MenuItem::add(array(
            'plugin'   => $this->_plugin,
            'name'     => 'main-ticket',
            'labelKey' => $this->_plugin.'.main-menu-title',
        ));

        MenuItem::add(array(
            'plugin'   => $this->_plugin,
            'name'     => 'project',
            'labelKey' => $this->_plugin.'.menu-project-title',
            'action'   => 'htracker-project-index',
            'parentId' => $menu->id,
        ));

        MenuItem::add(array(
            'plugin'   => $this->_plugin,
            'name'     => 'ticket',
            'labelKey' => $this->_plugin.'.menu-ticket-title',
            'action'   => 'htracker-index',
            'parentId' => $menu->id,
        ));
    }


    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate() {
        $items = MenuItem::getPluginMenuItems($this->_plugin);
        foreach($items as $item){
            $item->delete();
        }

        $permissions = Permission::getPluginPermissions($this->_plugin);
        foreach($permissions as $permission){
            $permission->delete();
        }

    }


    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if
     * configurable.
     */
    public function settings() {
        $param = array(
            'id' => 'ticket-settings-form',
            'fieldsets' => array(
                'status'  => array(
                    'legend' => Lang::get($this->_plugin.'.settings-status-legend'),

                    new ButtonInput(array(
                        'name'       => 'addStatus',
                        'class'      => 'btn-success',
                        'icon'       => 'plus',
                        'label'      => Lang::get($this->_plugin.'.settings-add-status-btn'),
                        'attributes' => array('ko-click' => 'add'),
                    )),

                    new ObjectInput(array(
                        'name'       => 'options',
                        'hidden'     => true,
                        'attributes' => array('ko-value' => 'ko.toJSON(options())'),
                        'default'    => Option::get($this->_plugin.'.status'),
                    ))
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name'  => 'valid',
                        'value' => Lang::get('main.valid-button'),
                    )),

                    new ButtonInput(array(
                        'name'    => 'cancel',
                        'value'   => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")',
                    )),
                ),
            ),
            'onsuccess' => 'app.dialog("close");',
        );

        $form = new Form($param);

        if(!$form->submitted()) {
            Controller::current()->addJavaScript(Plugin::current()->getJsUrl('settings.js'));
            Controller::current()->addCss(Plugin::current()->getCssUrl('settings.less'));
            Controller::current()->addKeysToJavaScript($this->_plugin.'.settings-confirm-delete-status');

            $content = View::make(
                Plugin::current()->getView('settings.tpl'),
                array('form' => $form)
            );

            return $form->wrap($content);
        }
        else {
            if($form->check()) {
                Option::set($this->_plugin . '.status', $form->getData('options'));

                return $form->response(Form::STATUS_SUCCESS);
            }
        }
    }
}
