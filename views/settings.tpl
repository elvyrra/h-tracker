<legend>{{ $form->fieldsets['status']->legend }}</legend>
<p>{text key="h-tracker.settings-status-intro"}</p>

{{ $form->inputs['addStatus'] }}
{{ $form->inputs['options'] }}

<div ko-foreach="orderedOptions" class="form-group">
    <div class="input-group status-line">
        <div class="input-group-addon status-position">
            <div class="icon icon-chevron-up pointer status-up" ko-click="$parent.up.bind($parent)"></div>
            <div class="icon icon-chevron-down pointer status-down" ko-click="$parent.down.bind($parent)"></div>
        </div>

        <input type="text" ko-value="label" ko-event="{change : $parent.setLabel.bind($parent)}" class="status-label form-control"/>

        <div class="input-group-addon pointer" ko-click="$parent.remove.bind($parent)" >
            <i class="icon icon-trash text-danger" class="status-remove"></i>
        </div>
    </div>
</div>

{{ $form->fieldsets['submits'] }}