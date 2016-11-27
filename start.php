<?php

namespace Hawk\Plugins\HTracker;

App::router()->prefix('/htracker', function () {
    App::router()->auth( App::session()->isAllowed('h-tracker.manage-ticket'), function () {
        /*
         * Tickets
         */
        App::router()->get('htracker-index', '/index', array('action' => 'TicketController.index'));

        App::router()->any('htracker-editTicket', '/tickets/{ticketId}', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.edit'));

        // Comments
        App::router()->get('htracker-history', '/tickets/{ticketId}/history', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.history'));

        App::router()->post('htracker-editComment', '/tickets//{ticketId}/comment/{commentId}', array('where' => array('commentId' => '\d+', 'ticketId' => '\d+'), 'action' => 'TicketController.editComment'));

        /*
         * Projects
         */
        App::router()->get('htracker-project-index', '/projects', array('action' => 'ProjectController.index'));

        App::router()->any('htracker-editProject', '/projects/{projectId}', array('where' => array('projectId' => '\d+'), 'action' => 'ProjectController.edit'));
    });
});
