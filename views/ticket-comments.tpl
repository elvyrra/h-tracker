<div class="h-tracker-comments" id="{{ $id }}">
    {foreach($comments as $comment)}
        <div class="row comment">
            {widget plugin="h-widgets" class="MetaData" size="small" userId="{$comment->author}" meta="{$comment->meta}" description="{$comment->description}"}
        </div>
    {/foreach}

    <div class="row">
        {widget plugin="h-widgets" class="CommentForm" onsuccess="{$onsuccess}" action="{$action}" title="{text key='h-tracker.comment-form-title'}"}
    </div>
</tr>