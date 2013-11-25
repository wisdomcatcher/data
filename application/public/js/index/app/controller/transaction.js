Ext.define('cwc.controller.transaction', {
    extend: 'cwc.controller.base',
    views: [
        'transaction.index',
        'transaction.add',
        'transaction.edit'
    ],
    stores: [
        'transaction'
    ],
    init: function() 
    {
        this.control({
            'cwc_transaction_add button[action=add]': {
                click: this.add_db_record
            },
            'cwc_transaction_edit button[action=save]': {
                click: this.update_db_record
            },
            'cwc_transaction_list' : {
                headerfilterchange : function(grid, filters, last_filters, active) {
                    grid.getStore().loadPage(1);
                },
                'cwc_transaction_add' : function()
                {
                    this.open_add_form();
                },
                'cwc_transaction_edit' : function(grid, rec)
                {
                    this.open_edit_form({id: rec.get('id')});
                }
            }
        });
    }
});