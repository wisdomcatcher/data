Ext.define('cwc.controller.entity', {
    extend: 'cwc.controller.base',
    views: [
        'entity.list',
        //'data.add',
        //'entity.edit'
    ],
    init: function() 
    {
        this.control({
            /*'cwc_entity_list' : {
                'cwc_data_add' : function(entity)
                {
                    //this.open_add_form();
                    Ext.widget('cwc_data_add', {entity: entity});
                },
                'cwc_transaction_edit' : function(grid, rec)
                {
                    this.open_edit_form({id: rec.get('id')});
                }
            },*/
            //'cwc_data_add button[action=add]': {
            //    click: this.add_db_record
            //},
        });
    },
    listAction: function(params) {

        Ext.Ajax.request({
            url     : '/entity/get',
            method  : 'POST',
            scope   : this,
            params  : { 
                id : params.id
            },
            success : function(response) {
                var response = Ext.JSON.decode(response.responseText);
                params.entity = response.data;
                ProcessView(this, this.id + '.list', params);
            }
        }, this);

    }
});