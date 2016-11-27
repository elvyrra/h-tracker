<p> A new comment was left by {{ $author }} on the task #{{ $ticketId }} : </p>

<div>{{ $comment }}</div>

See the task : <a href="{{ ROOT_URL }}{uri action='htracker-editTicket' ticketId='{$ticketId}'}">{{ $title }}</a>