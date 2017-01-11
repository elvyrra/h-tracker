<?php

namespace Hawk\Plugins\HTracker;

class TicketController extends Controller {


    /**
     * Entry point for the page of tickets
     */
    public function index() {
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
            'lineClass' => function($line) {
                if($line->isLate()) {
                    return 'text-danger';
                }

                return '';
            },
            'fields' => array(
                'id' => array(
                    'label' => Lang::get($this->_plugin.'.ticket-list-id-label'),
                    'display' => function ($value, $field, $line) {
                        return '#' . $value;
                    },
                    'href' => function ($value, $field, $ticket) {
                        return App::router()->getUri('htracker-editTicket', array('ticketId' => $ticket->id));
                    },
                ),

                'title' => array(
                    'label' => Lang::get($this->_plugin . '.ticket-list-title-label'),
                    'href'  => function ($value, $field, $ticket) {
                        return App::router()->getUri('htracker-editTicket', array('ticketId' => $ticket->id));
                    }
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
                    'display' => function ($value, $field, $ticket) use ($users) {
                        if(empty($value)) {
                            return '';
                        }

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
                    'display' => function ($value, $field, $ticket) use ($status) {
                        // return isset($status[$value]) ? $status[$value] : '';

                        $select = new SelectInput(array(
                            'options' => $status,
                            'class' => 'task-status',
                            'value' => $value,
                            'attributes' => array(
                                'data-task-id' => $ticket->id
                            )
                        ));

                        return $select->display();
                    },
                ),

                'deadLine' => array(
                    'label'   => Lang::get($this->_plugin . '.ticket-list-deadline-label'),
                    'display' => function ($value, $field, $line) {
                        $date = date(Lang::get('main.date-format'), strtotime($value));
                        $result = $date;

                        if($line->isLate()) {
                            $result .= Icon::make(array(
                                'icon' => 'clock-o',
                                'class' => 'pull-right',
                                'title' => Lang::get($this->_plugin . '.ticket-list-over-delayed-title', array(
                                    'date' => $date
                                ))
                            ));
                        }

                        return $result;
                    },
                    'search'  => array(
                        'type' => 'date'
                    )
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
                'icon' => $this->getPlugin()->getFaviconUrl(),
                'tabId' => 'htracker-tasks-index'
            ));
        }

        return $list->display();
    }



    /**
     * Edit a ticket
     */
    public function edit() {
        // Options select
        $projects = $this->getProjectsOptions();
        $users = $this->getUsersOptions();
        $status = $this->getStatusOptions();

        $param = array(
            'id' => 'htracker-ticket-form',
            'model' => 'Ticket',
            'reference' => array('id' => $this->ticketId),
            'columns' => 2,
            'fieldsets' => array(
                'general' => array(
                    new TextInput(array(
                        'name'     => 'title',
                        'required' => true,
                        'label'    => Lang::get($this->_plugin . '.ticket-form-title-label'),
                    )),

                    new SelectInput(array(
                        'name' => 'projectId',
                        'options' => $projects,
                        'label' => Lang::get($this->_plugin . '.ticket-form-project-label'),
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
                        'name'       => 'target',
                        'invitation' => ' - ',
                        'options'    => $users,
                        'emptyValue' => '0',
                        'label'      => Lang::get($this->_plugin . '.ticket-form-target-label'),
                    )),

                    new DatetimeInput(array(
                        'name'  => 'deadLine',
                        'label' => Lang::get($this->_plugin . '.ticket-form-dead-line-label'),
                        'default' => date('Y-m-d'),
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

                'description' => array(
                    'legend' => Lang::get($this->_plugin . '.ticket-form-description-label'),
                    Plugin::get('h-widgets') ?
                        new \Hawk\Plugins\HWidgets\MarkdownInput(array(
                            'name'  => 'description',
                            'rows' => 5
                        )) :
                        new WysiwygInput(array(
                            'name'  => 'description'
                        ))
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
                    'history' => $this->ticketId ? $this->history() : ''
                )
            );

            return NoSidebarTab::make(array(
                'page'  => $display,
                'title' => $this->ticketId ?
                    Lang::get($this->_plugin . '.ticket-form-title', array('id' => $this->ticketId, 'title' => $form->getData('title'))) :
                    Lang::get($this->_plugin.'.ticket-new-form-title'),
                'icon'  => 'book',
            ));
        }
        else{
            return $form->treat();
        }
    }


    /**
     * Display the list of comments for a ticket
     */
    public function history() {
        $comments = TicketComment::getListByExample(new DBExample(array(
            'ticketId' => $this->ticketId
        )));

        $parser = new Parsedown();

        foreach($comments as &$comment) {
            $comment->meta = Lang::get($this->_plugin . '.comment-meta-title', array(
                'author' => User::getById($comment->author)->username,
                'ago' => Utils::timeAgo($comment->ctime)
            ));

            $comment->description = $parser->text($comment->description);
        }

        $id = 'h-tracker-comments-' . uniqid();

        $this->addCss($this->getPlugin()->getCssUrl('htracker.less'));

        return View::make($this->getPlugin()->getView('ticket-comments.tpl'), array(
            'comments' => $comments,
            'id' => $id,
            'action' => App::router()->getUri('htracker-editComment', array(
                'ticketId' => $this->ticketId,
                'commentId' => 0
            )),
            'onsuccess' => '$("#' . $id . '").load(app.getUri("htracker-history", {
                ticketId : ' . $this->ticketId . '
            }))'
        ));
    }


    /**
     * Edit a ticket comment
     */
    public function editComment() {
        App::response()->setContentType('json');

        $comment = TicketComment::add(array(
            'author' => App::session()->getUser()->id,
            'ticketId' => $this->ticketId,
            'ctime' => time(),
            'description' => App::request()->getBody('content')
        ));

        // Send a notification of the new comment on the task
        $ticket = Ticket::getById($this->ticketId);
        $project = Project::getById($ticket->projectId);
        $author = App::session()->getUser()->username;

        $subject = Lang::get($this->_plugin . '.notif-new-comment', array(
            'project' => $project->name,
            'author' => $author,
            'id' => $ticket->id
        ));
        $content = View::make($this->getPlugin()->getView('notifications/new-comment.tpl'), array(
            'author' => $author,
            'ticketId' => $ticket->id,
            'comment' => $comment->description,
            'title' => $ticket->title,
        ));

        $recipients = array_filter(User::getAll('id'), function($user) {
            return $user->isAllowed($this->_plugin . '.manage-ticket');
        });

        foreach($recipients as $recipient) {
            $email = new Mail();
            $email  ->subject($subject)
                    ->content($content)
                    ->to($recipient->email)
                    ->send();
        }

        return array();
    }


    /**
     * Get the possible users that can be ticket / comment author
     *
     * @return array
     */
    private function getUsersOptions() {
        $users = array_filter(User::getAll('id'), function($user) {
            return $user->isAllowed($this->_plugin . '.manage-ticket');
        });

        return array_map(
            function ($a) {
                return $a->username;
            },
            $users
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

    /**
     * Update the status of a task. This method is called from the tasks list
     * @return array
     */
    public function updateStatus() {
        $task = Ticket::getById($this->ticketId);

        $task->status = App::request()->getBody('status');

        $task->save();

        App::response()->setContentType('json');
        return array(
            'message' => Lang::get($this->_plugin . '.update-ticket-status-success')
        );
    }
}
