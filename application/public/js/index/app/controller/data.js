Ext.define('cwc.controller.data', {
    extend: 'cwc.controller.base',
    views: [
        'data.index',
        'data.add',
        'data.edit'
    ],
    init: function() 
    {
        this.control({
            'cwc_data_list' : {
                'cwc_data_add' : function(entity)
                {
                    Ext.widget('cwc_data_add', {entity: entity});
                },
                'cwc_data_edit' : function(grid, rec, entity)
                {
                    //this.open_edit_form({id: rec.get('id'), entity: entity});
                    var view = Ext.widget('cwc_data_edit', {myparams: {id: rec.get('id')}, entity: entity});
                    this.load_edit_form(view, rec.get('id'), {entity_id: entity.id});
                },
                headerfilterchange : function(grid, filters, last_filters, active) {
                    grid.getStore().loadPage(1);
                },
            },
            'cwc_data_add button[action=add]': {
                click: this.add_db_record
            },
            'cwc_data_edit button[action=save]': {
                click: this.update_db_record
            },
        });
    },
    indexAction: function(params) {

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
                ProcessView(this, this.id + '.index', params);
            }
        }, this);

    }
});