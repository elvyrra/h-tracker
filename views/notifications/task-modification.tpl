<p> The task #{{ $ticketId }} has been modified by {{ $author }} : </p>

<a href="{{ ROOT_URL }}{uri action='htracker-editTicket' ticketId='{$ticketId}'}">{{ $title }}</a>