<?php

namespace Hawk\Plugins\HTracker;

class TicketController extends Controller {


    /**
     * Entry point for the page of tickets
     */
    public function index()
    {
        // Get list of all subject
        $projects = $this->getProjectsOptions();
        $users = $this->getUsersOptions();
        $status = $this->getStatusOptions();

        $filters = TicketFilterWidget::getInstance()->getFilters();
        $filter = null;

        if(!empty($filters['status'])) {
            $filter = new DBExample(array(
                'status' => array('$in' => array_keys($filters['status'])),
            ));
        }

        $param = array(
            'id' => 'htracker-ticket-list',
            'model' => 'Ticket',
            'filter' => $filter,
            'reference' => 'id',
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.new-ticket-btn'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('htracker-editTicket', array('ticketId' => 0)),
                    'disabled' => empty($projects),
                    'title' => empty($projects) ? Lang::get($this->_plugin.'.no-project-created') : '',
                ),

                array(
                    'icon' => 'cubes',
                    'label' => Lang::get($this->_plugin . '.new-project-btn'),
                    'class' => 'btn-primary',
                    'href' => App::router()->getUri('htracker-editProject', array('projectId' => 0)),
                    'target' => 'dialog',
                ),
            ),
            'fields' => array(
                'id' => array(
                    'label' => Lang::get($this->_plugin.'.ticket-list-id-label'),
                    'display' => function ($value) {
                        return '#'.$value;
                    },
                    'href' => function ($value, $field, $ticket) {
                        return App::router()->getUri('htracker-editTicket', array('ticketId' => $ticket->id));
                    },
                ),

                'title' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-list-title-label'),
                    'href'  => function ($value, $field, $ticket) {
                        return App::router()->getUri('htracker-editTicket', array('ticketId' => $ticket->id));
                    },
                ),

                'projectId' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-list-project-label'),
                    'display' => function ($value, $field, $ticket) {
                        return Project::getById($value)->name;
                    },
                    'search'  => array(
                        'type'       => 'select',
                        'options'    => $projects,
                        'invitation' => ' - ',
                    ),
                ),

                'target' => array(
                    'label'   => Lang::get($this->_plugin . '.ticket-list-target-label'),
                    'display' => function ($value, $field, $ticket) {
                        $user = User::getById($value);
                        if($user) {
                            return $user->username;
                        }
                        else {
                            return ' ';
                        }
                    },
                    'search'  => array(
                        'type'       => 'select',
                        'invitation' => ' - ',
                        'options'    => $users,
                    ),
                ),

                'priority' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-list-priority-label'),
                    'display' => function ($value, $field, $line) {
                        return Lang::get($this->_plugin . '.ticket-priority-'.(string) $value);
                    },
                    'search' => array(
                        'type' => 'select',
                        'invitation' => ' - ',
                        'options' => Ticket::getPrioritiesList(),
                    ),
                ),

                'status' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-list-status-label'),
                    'search' => false,
                    'display' => function ($value) use ($status) {
                        return isset($status[$value]) ? $status[$value] : '';
                    },
                ),

                'mtime' => array(
                    'label'   => Lang::get($this->_plugin . '.ticket-list-mtime-label'),
                    'display' => function ($value, $field) {
                        return date(Lang::get('main.time-format'), $value);
                    },
                    'search'  => false,
                ),

                'deadLine' => array(
                    'hidden' => true
                ),
            )
        );

        $list = new ItemList($param);

        if(!$list->isRefreshing()) {
            // Add css file
            $this->addCss(Plugin::current()->getCssUrl('htracker.less'));

            // Add javascript file
            $this->addJavaScript(Plugin::current()->getJsUrl('htracker.js'));

            return LeftSidebarTab::make(array(
                'page' => array(
                    'content' => $list->display()
                ),
                'sidebar' => array(
                    'widgets' => array(TicketFilterWidget::getInstance()),
                ),
                'title' => Lang::get($this->_plugin.'.ticket-list-title'),
                'icon' => 'book',
            ));
        }
        else {
            return $list->display();
        }
    }



    /**
     * Edit a ticket
     */
    public function edit(){

        // Options select
        $projects = $this->getProjectsOptions();
        $users = $this->getUsersOptions();
        $status = $this->getStatusOptions();

        $param = array(
            'id' => 'htracker-ticket-form',
            'model' => 'Ticket',
            'reference' => array('id' => $this->ticketId),
            'fieldsets' => array(
                'general' => array(
                    new SelectInput(array(
                        'name' => 'projectId',
                        'options' => $projects,
                        'label' => Lang::get($this->_plugin . '.ticket-form-project-label'),
                    )),

                    new TextInput(array(
                        'name'     => 'title',
                        'required' => true,
                        'label'    => Lang::get($this->_plugin . '.ticket-form-title-label'),
                    )),

                    new WysiwygInput(array(
                        'name'  => 'description',
                        'label' => Lang::get($this->_plugin . '.ticket-form-description-label'),
                    )),

                    new SelectInput(array(
                        'name'    => 'priority',
                        'label'   => Lang::get($this->_plugin.'.ticket-form-priority-label'),
                        'options' => Ticket::getPrioritiesList(),
                    )),

                    new SelectInput(array(
                        'name'    => 'status',
                        'options' => $status,
                        'label'   => Lang::get($this->_plugin . '.ticket-form-status-label'),
                    )),

                    new SelectInput(array(
                        'name'    => 'target',
                        'options' => $users,
                        'label'   => Lang::get($this->_plugin . '.ticket-form-target-label'),
                    )),

                    new DatetimeInput(array(
                        'name'  => 'deadLine',
                        'label' => Lang::get($this->_plugin . '.ticket-form-dead-line-label'),
                        'value' => date('Y-m-d'),
                    )),

                    new HiddenInput(array(
                        'name'  => 'author',
                        'value' => App::session()->getUser()->id,
                    )),

                    new HiddenInput(array(
                        'name'    => 'ctime',
                        'default' => time(),
                    )),

                    new HiddenInput(array(
                        'name'  => 'mtime',
                        'value' => time(),
                    )),
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name'  => 'valid',
                        'value' => Lang::get('main.valid-button'),
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => ! $this->ticketId,
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'href' => App::router()->getUri('htracker-index')
                    )),
                ),
            ),
            'onsuccess' => 'app.load(app.getUri("htracker-index"));',
        );

        $form = new Form($param);

        if(!$form->submitted()) {
            $display = View::make(
                Plugin::current()->getView("ticket-form.tpl"), array(
                    'form' => $form,
                    'history' => $this->history()
                )
            );

            return NoSidebarTab::make(array(
                'page'  => $display,
                'title' => $this->ticketId ?
                    Lang::get($this->_plugin . '.ticket-form-title',array('id' => $this->ticketId)) :
                    Lang::get($this->_plugin.'.ticket-new-form-title'),
                'icon'  => 'book',
            ));
        }
        else{
            if($form->submitted() === "delete") {
                return $form->delete();
            }
            else if($form->check()) {
                $oldValues = Ticket::getById($this->ticketId);

                if($oldValues) {
                    $comments = array();
                    foreach(array('title', 'description') as $key){
                        if($oldValues->$key !== $form->getData($key)) {
                            // $form->fields['status']->dbvalue()
                            $comments[] = Lang::get($this->_plugin . '.'.$key.'-change-comment', array('oldValue' => $oldValues->$key, 'newValue' => $form->getData($key)));
                        }
                    }

                    if($oldValues->deadLine !== $form->inputs['deadLine']->dbvalue()) {
                        $comments[] = Lang::get(
                            $this->_plugin . '.deadLine-change-comment',
                            array(
                                'oldValue' => date(Lang::get('main.date-format'), strtotime($oldValues->deadLine)),
                                'newValue' => date(Lang::get('main.date-format'), strtotime($form->inputs['deadLine']->dbvalue())),
                            )
                        );
                    }

                    if($oldValues->status !== $form->getData('status')) {
                        $oldValue   = $oldValues->status;
                        $newValue   = $form->getData('status');
                        $comments[] = Lang::get(
                            $this->_plugin . '.status-change-comment',
                            array(
                                'oldValue' => $status[$oldValue],
                                'newValue' => $status[$newValue]
                            )
                        );
                    }

                    if($oldValues->target !== $form->getData('target')) {
                        $oldValue   = $oldValues->target;
                        $newValue   = $form->getData('target');
                        $comments[] = Lang::get(
                            $this->_plugin . '.target-change-comment',
                            array(
                                'oldValue' => $users[$oldValue],
                                'newValue' => $users[$newValue]
                            )
                        );
                    }

                    if(!empty($comments)) {
                        TicketComment::add(array(
                            'author' => App::session()->getUser()->id,
                            'ticketId' => $this->ticketId,
                            'ctime' => time(),
                            'description' => implode('<br />', $comments),
                        ));
                    }
                }

                $form->register(Form::NO_EXIT);

                return $form->response(Form::STATUS_SUCCESS);
            }
        }
    }


    /**
     * Display the list of comments for a ticket
     */
    public function history() {
        $paramList = array(
            'id' => 'htracker-ticket-history',
            'model' => 'TicketComment',
            'action' => App::router()->getUri('htracker-history', array('ticketId' => $this->ticketId)),
            'filter' => new DBExample(array(
                'ticketId' => $this->ticketId,
            )),
            'controls' => $this->ticketId ? array(
                array(
                    'icon'   => 'plus',
                    'label'  => Lang::get($this->_plugin . '.new-comment-btn'),
                    'class'  => 'btn-success',
                    'href'   => App::router()->getUri('htracker-editComment', array('ticketId' => $this->ticketId, 'commentId' => 0)),
                    'target' => 'dialog',
                )) :
                array(),
            'fields' => array(
                'description' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-history-description-label'),
                ),

                'author' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-history-author-label'),
                    'display' => function ($value, $field, $ticket) {
                        return User::getById($value)->username;
                    },
                ),

                'ctime' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-history-ctime-label'),
                    'display' => function ($value) {
                        return date(Lang::get('main.time-format'), $value);
                    },
                    'search'  => false,
                ),
            ),
        );

        $list = new ItemList($paramList);

        return $list->display();
    }


    /**
     * Edit a ticket comment
     */
    public function editComment() {
        $param = array(
            'id' => 'ticket-form-comment',
            'model' => 'TicketComment',
            'reference' => array('id' => $this->commentId),
            'fieldsets' => array(
                'general' => array(
                    new WysiwygInput(array(
                        'name' => 'description'
                    )),

                    new HiddenInput(array(
                        'name'  => 'ticketId',
                        'value' => $this->ticketId,
                    )),

                    new HiddenInput(array(
                        'name'  => 'author',
                        'value' => App::session()->getUser()->id,
                    )),

                    new HiddenInput(array(
                        'name'    => 'ctime',
                        'default' => time(),
                    )),
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

            'onsuccess' => 'app.dialog("close"); app.lists["htracker-ticket-history"].refresh();',
        );

        $form = new Form($param);

        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'page'  => $form,
                'title' => Lang::get($this->_plugin . '.ticket-comment-form-title'),
            ));
        }
        else {
            return $form->treat();
        }
    }


    /**
     * Get the possible users that can be ticket / comment author
     *
     * @return array
     */
    private function getUsersOptions() {
        return array_map(
            function ($a) {
                return $a->username;
            },
            User::getAll('id')
        );

    }


    /**
     * Get the projects
     *
     * @return array
     */
    private function getProjectsOptions() {
        return array_map(
            function ($a) {
                return $a->name;
            },
            Project::getAll('id')
        );

    }


    /**
     * Get the options for the ticket status
     *
     * @return array
     */
    private function getStatusOptions(){
        $options = json_decode(Option::get($this->_plugin . '.status'));
        usort(
            $options,
            function ($a, $b) {
                return ($a->order - $b->order);
            }
        );

        $status = array();
        foreach($options as $option){
            $status[$option->id] = $option->label;
        }

        return $status;
    }
}
