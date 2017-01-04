<legend>{{ $form->fieldsets['status']->legend }}</legend>
<p>{text key="h-tracker.settings-status-intro"}</p>

{{ $form->inputs['addStatus'] }}
{{ $form->inputs['options'] }}

<div class="form-group">
    <div class="input-group status-line" e-each="{$data : options, $sort : 'order'}">
        <div class="input-group-addon status-position">
            <div class="icon icon-chevron-up pointer status-up" e-click="$root.up.bind($root)"></div>
            <div class="icon icon-chevron-down pointer status-down" e-click="$root.down.bind($root)"></div>
        </div>

        <input type="text" e-value="label" class="status-label form-control"/>

        <div class="input-group-addon pointer" e-click="$root.remove.bind($root)" >
            <i class="icon icon-trash text-danger" class="status-remove"></i>
        </div>
    </div>
</div>

{{ $form->fieldsets['submits'] }}