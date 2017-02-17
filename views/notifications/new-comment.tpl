<p> {text key="h-tracker.notif-new-comment-txt" id="{$ticketId}" author="{$author}" } </p>

<div>{{ $comment }}</div>

{text key="h-tracker.notif-see-the-task"} <a href="{{ ROOT_URL }}#!{uri action='htracker-editTicket' ticketId='{$ticketId}'}">{{ $title }}</a>