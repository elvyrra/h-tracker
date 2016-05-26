/* global app, ko, Lang */

'use strict';

require(['app'], function() {
    var form = app.forms['ticket-settings-form'];

    var StatusModel = function() {
        this.options = ko.observableArray(JSON.parse(form.inputs.options.val()));

        this.orderedOptions = ko.computed(function() {
            return this.options().sort(function(a, b) {
                return a.order - b.order;
            });
        }.bind(this));

        this.ids = ko.computed(function() {
            return ko.utils.arrayMap(
                this.options(),
                function(item) {
                    return parseInt(item.id);
                }
            );
        }.bind(this));

        this.maxId = ko.computed(function() {
            return Math.max.apply(null, this.ids());
        }.bind(this));

        this.orders = ko.computed(function() {
            return ko.utils.arrayMap(
                this.options(),
                function(item) {
                    return parseInt(item.order);
                }
            );
        }.bind(this));

        this.maxOrder = ko.computed(function() {
            return Math.max.apply(null, this.orders());
        }.bind(this));
    };

    StatusModel.prototype.add = function() {
        this.options.push({
            id : this.maxId() + 1,
            order : this.maxOrder() + 1,
            label : ''
        });
    };

    StatusModel.prototype.up = function(item) {
        // Change the order with the last upper item
        var upperItems = this.orderedOptions().filter(function(a) {
            return a.order < item.order;
        });

        var upItem = upperItems[upperItems.length - 1];

        if(upItem) {
            var tmp = upItem.order;

            upItem.order = item.order;
            item.order = tmp;

            this.rebuild();
        }
    };

    StatusModel.prototype.down = function(item) {
        // Change the order with the last upper item
        var lowerItems = this.orderedOptions().filter(function(a) {
            return a.order > item.order;
        });

        var downItem = lowerItems[0];

        if(downItem) {
            var tmp = downItem.order;

            downItem.order = item.order;
            item.order = tmp;

            this.rebuild();
        }
    };

    StatusModel.prototype.setLabel = function(item, event) {
        item.label = event.target.value;

        this.rebuild();
    };

    StatusModel.prototype.remove = function(item) {
        if(confirm(Lang.get('ticket.settings-confirm-delete-status'))) {
            this.options.splice(this.options().indexOf(item), 1);
        }
    };

    StatusModel.prototype.rebuild = function() {
        this.options.valueHasMutated();
    };

    var status = new StatusModel();

    ko.applyBindings(status, form.node.get(0));
});