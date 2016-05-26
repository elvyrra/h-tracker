/* global app */

'use strict';

require(['app'], function() {
    app.forms['ticket-filter-form'].onchange = function() {
        app.lists['htracker-ticket-list'].refresh(
            {
                headers : {
                    'X-List-Filter' : this.toString()
                }
            }
        );
    };
});