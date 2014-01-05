/**
* Ext.ux.grid.PageSize
*/
Ext.define('cwc.ux.PageSize', 
{
    extend      : 'Ext.form.field.ComboBox',
    alias       : 'plugin.pagesize',
    beforeText  : 'Показать',
    afterText   : 'строк/страницу',
    mode        : 'local',
    displayField: 'text',
    valueField  : 'value',
    allowBlank  : false,
    triggerAction: 'all',
    width       : 100,
    maskRe      : /[0-9]/,    

    init: function(paging) 
    {
        paging.on('afterrender', this.onInitView, this);
    },
    store: new Ext.data.SimpleStore(
    {
        fields: ['text', 'value'],
        data: [['5', 5], ['10', 10], ['15', 15], ['20', 20], ['25', 25], ['50', 50], ['100', 100], ['200', 200], ['500', 500]]
    }),    
    onInitView: function(paging) 
    {
        this.setValue(paging.store.pageSize); 
        paging.add('-', this.beforeText, this, this.afterText);
        this.on('select', this.onPageSizeChanged, paging);
        this.on('specialkey', function(combo, e) {
            if(13 === e.getKey()) {
                this.onPageSizeChanged.call(paging, this);        
            }
        });
    },
    onPageSizeChanged: function(combo) 
    {
        this.store.pageSize = parseInt(combo.getRawValue(), 10);
        this.doRefresh();
    }
}); 