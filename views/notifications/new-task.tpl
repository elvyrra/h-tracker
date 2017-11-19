<p>{text key="h-tracker.notif-new-task-txt" author="{$author}" project="{$project}"}</p>

<a href="{{ ROOT_URL }}#!{uri action='htracker-editTicket' ticketId='{$ticketId}'}">{{ $title }}</a>

<ul>
    {foreach($details as $key => $value)}
        <li><b>{{ $key }}</b> : {{ $value }}</li>
    {/foreach}
</ul>