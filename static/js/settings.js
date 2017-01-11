'use strict';

require(['app', 'emv', 'lang'], function(app, EMV, Lang) {
    const form = app.forms['ticket-settings-form'];

    /**
     * This class manages the behavior of the settings form for the plugin h-tracker
     */
    class StatusModel extends EMV {
        /**
         * Constructor
         */
        constructor() {
            super({
                data : {
                    options : JSON.parse(form.inputs.options.val()),
                    fixedIds : [1, 2]
                },
                computed : {
                    ids : function() {
                        return this.options.map((item) => {
                            return parseInt(item.id, 10);
                        });
                    },
                    maxId : function() {
                        return Math.max.apply(null, this.ids);
                    },
                    orders : function() {
                        return this.options.map((item) => {
                            return parseInt(item.order, 10);
                        });
                    },
                    maxOrder : function() {
                        return Math.max.apply(null, this.orders);
                    }
                }
            });
        }

        /**
         * Add an option
         */
        add() {
            this.options.push({
                id : this.maxId + 1,
                order : this.maxOrder + 1,
                label : ''
            });
        }

        /**
         * Change the order of the item with the last upper item
         * @param   {Object} item The item to up
         */
        up(item) {
            var upperItems = this.options.filter((a) => {
                return a.order < item.order;
            });

            var upItem = upperItems[upperItems.length - 1];

            if(upItem) {
                var tmp = upItem.order;

                upItem.order = item.order;
                item.order = tmp;
            }
        }

        /**
         * Change the order of the item with the first lower item
         * @param   {Object} item The item to down
         */
        down(item) {
            var lowerItems = this.options.filter((a) => {
                return a.order > item.order;
            });

            var downItem = lowerItems[0];

            if(downItem) {
                var tmp = downItem.order;

                downItem.order = item.order;
                item.order = tmp;
            }
        }

        /**
         * Set the label of an item
         * @param {Object} item  The item to set its label
         * @param {Event}  event The initial event that triggered this method
         */
        setLabel(item, event) {
            item.label = event.target.value;
        }

        /**
         * Remove an item
         * @param   {Object} item The item to remove
         */
        remove(item) {
            if(confirm(Lang.get('h-tracker.settings-confirm-delete-status'))) {
                const index = this.options.indexOf(item);

                this.options.splice(index, 1);
            }
        }
    }

    var status = new StatusModel();

    status.$apply(form.node.get(0));
});