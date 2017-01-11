'use strict';

require(['app', 'emv', 'jquery'], (app, EMV, $) => {
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


    /**
     * Manage the list of tasks
     *
     * @param {int} id The ticket id
     * @param {int} status The status id to set
     */
    $('#htracker-ticket-list').on('change', '.task-status', function() {
        const ticketId = $(this).data('task-id');
        const value = $(this).val();

        app.loading.start();

        $.ajax({
            url : app.getUri('htracker-ticket-status', {
                ticketId : ticketId
            }),
            method : 'patch',
            data : {
                status : value
            },
            dataType : 'json'
        })

        .done((response) => {
            app.notify('success', response.message);
            app.lists['htracker-ticket-list'].refresh();
        })

        .fail((xhr) => {
            if (!xhr.responseJSON) {
                // The returned result is not a JSON
                app.notify('error', xhr.responseText);
            }
            else {
                app.notify('error', xhr.responseJSON.message);
            }
        })

        .always(() => {
            app.loading.stop();
        });
    });
});
