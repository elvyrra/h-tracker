'use strict';

require(['app', 'emv'], (app, EMV) => {
    /**
     * This controller manages the form that filters the tasks
     */
    class FilterForm extends EMV {
        /**
         * Change the list filters
         */
        setFilter() {
            app.lists['htracker-ticket-list'].refresh(
                {
                    headers : {
                        'X-List-Filter' : app.forms['ticket-filter-form'].toString()
                    }
                }
            );
        }
    }

    const filterForm = new FilterForm();

    filterForm.$apply(document.getElementById('ticket-filter-form'));
});
