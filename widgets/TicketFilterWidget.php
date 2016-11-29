<?php
/**
 * TicketFilterWidget.class.php
 *
 * @author Elvyrra SAS
 */

namespace Hawk\Plugins\HTracker;

/**
 * This Widget is used to filter the users list by status or role
 */
class TicketFilterWidget extends Widget {
    public static $filters = array('status');


    public function getFilters() {

        if(App::request()->getHeaders('X-List-Filter')) {
            App::session()->getUser()->setOption($this->_plugin . '.ticket-list-filter', App::request()->getHeaders('X-List-Filter'));
        }

        $result = App::session()->getUser()->getOptions($this->_plugin . '.ticket-list-filter') ? json_decode(App::session()->getUser()->getOptions($this->_plugin . '.ticket-list-filter'), true) : array();
        foreach($result as $name => $values){
            $result[$name] = array_filter($result[$name]);
        }

        return($result);

    }


    public function display() {
        $filters = $this->getFilters();

        $form = new Form(array(
            'id' => 'ticket-filter-form',
            'attributes' => array(
                'e-on'  => '{change : setFilter}'
            ),
            'fieldsets' => array(
                'form' => array_map(
                    function ($status) use ($filters) {
                        return new CheckboxInput(
                            array(
                               'name' => 'status['.$status['id'].']',
                               'value' => isset($filters['status'][$status['id']]),
                               'label' => $status['label'],
                               'beforeLabel' => true,
                               'labelWidth'  => 'auto',
                            )
                        );
                    },
                    json_decode(Option::get($this->_plugin . '.status'), true)
                )
            )
        ));

        return View::make(Theme::getSelected()->getView("box.tpl"), array(
            'content' => $form,
            'title' => Lang::get($this->_plugin . '.ticket-filter-title'),
            'icon' => 'filter',
        ));
    }
}
