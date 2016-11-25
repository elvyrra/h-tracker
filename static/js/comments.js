'use strict';

require(['app', 'EMV'], (app, EMV) => {
    /**
     * THis class manage the comments of a ticjet in h-tracker
     */
    class CommentModel extends EMV {
        /**
         * Constructor
         */
        constructor() {
            super({
                comments : []
            });
        }
    }

    var comment = new CommentModel();

    comment.$apply();
});