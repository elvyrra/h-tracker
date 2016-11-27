<div id="ticket-form-main" >
    {{ $form }}

    {if($history)}
        <hr />

        <h3>{text key="h-tracker.history-title"}</h3>

        <div class="col-xs-12">
            {{ $history }}
        </div>
    {/if}
</div>