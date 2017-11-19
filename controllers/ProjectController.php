<?php

namespace Hawk\Plugins\HTracker;

use\Hawk\Plugins\HWidgets as HWidgets;

class ProjectController extends Controller{

    /**
     * Entry point for the page of ticket projects
     */
    public function index() {
        // Get list of all subject
        $list = new ItemList(array(
            'id' => 'htracker-project-list',
            'model' => 'Project',
            'customize' => array(
                'default' => ['name', 'description']
            ),
            'controls' => array(
                array(
                    'icon' => 'plus',
                    'label' => Lang::get($this->_plugin . '.new-project-btn'),
                    'class' => 'btn-success',
                    'href' => App::router()->getUri('htracker-editProject', array('projectId' => 0)),
                    'target' => 'dialog',
                ),
                array(
                    'icon' => 'eye',
                    'label' => Lang::get($this->_plugin . '.view-ticket-btn'),
                    'class' => 'btn-primary',
                    'href' => App::router()->getUri('htracker-index'),
                    'target' => 'newtab',
                ),
            ),
            'fields' => array(
                'name' => array(
                    'label' => Lang::get($this->_plugin . '.project-list-name-label'),
                    'href' => function($value, $field, $project){
                        return App::router()->getUri('htracker-editProject', array('projectId' => $project->id));
                    },
                    'target' => 'dialog'
                ),

                'description' => array(
                    'label' => Lang::get($this->_plugin . '.project-list-description-label'),
                ),

                'author' => array(
                    'label' => Lang::get($this->_plugin . '.project-list-author-label'),
                    'display' => function($value, $field, $ticket){
                        return User::getById($value)->username;
                    },
                ),

                'ctime' => array(
                    'label' => Lang::get($this->_plugin . '.project-list-ctime-label'),
                    'display' => function($value, $field){
                        return date(Lang::get('main.date-format'), $value);
                    },
                    'search' => false,
                ),
            )
        ));

        if(!$list->isRefreshing()) {
            // Add css file
            $this->addCss(Plugin::current()->getCssUrl('htracker.less'));

            return NoSidebarTab::make(array(
                'page' => $list->display(),
                'title' => Lang::get($this->_plugin . '.project-list-title'),
                'icon' => $this->getPlugin()->getFaviconUrl()
            ));
        }
        else {
            return $list->display();
        }

    }


    /**
     * Edit a project
     */
    public function edit() {

        $status = json_decode(Option::get($this->_plugin . '.status'));
        $options = array();

        foreach($status as $stat){
            $options[$stat->id] = $stat->label;
        }

        $param = array(
            'id' => 'htracker-project-form',
            'model' => 'Project',
            'reference' => array(
                'id' => $this->projectId
            ),
            'fieldsets' => array(
                'general' => array(

                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.project-form-name-label'),
                    )),

                    new HWidgets\MarkdownInput(array(
                        'name' => 'description',
                        'label' => Lang::get($this->_plugin . '.project-form-description-label'),
                        'labelWidth' => 'auto'
                    )),

                    new HiddenInput(array(
                        'name' => 'ctime',
                        'default' => time()
                    )),

                    new HiddenInput(array(
                        'name' => 'mtime',
                        'value' => time(),
                    )),

                    new HiddenInput(array(
                        'name' => 'author',
                        'value' => App::session()->getUser()->id,
                    ))
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new DeleteInput(array(
                        'name' => 'delete',
                        'value' => Lang::get('main.delete-button'),
                        'notDisplayed' => !$this->projectId
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'onclick' => 'app.dialog("close")'
                    ))
                ),
            ),
            'onsuccess' => 'app.dialog("close");
                			if(app.lists["htracker-project-list"]) {
                				app.lists["htracker-project-list"].refresh();
                			}'
        );

        $form = new Form($param);

        if(!$form->submitted()){
            return Dialogbox::make(array(
                'page' => $form,
                'title' => Lang::get($this->_plugin . '.project-form-title'),
                'icon' => 'book',
            ));
        }
        else{
            return $form->treat();
        }
    }
}