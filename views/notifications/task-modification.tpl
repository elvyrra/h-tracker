<p> {text key="h-tracker.notif-task-update-txt" id="{$ticketId}" author="{$author}"} </p>

<a href="{{ ROOT_URL }}#!{uri action='htracker-editTicket' ticketId='{$ticketId}'}">{{ $title }}</a>

<ul>
    {foreach($comments as $comment)}
        <li>{{ $comment }}</li>
    {/foreach}
</ul>