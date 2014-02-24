Ext.define('cwc.view.entity.index' ,{
    extend: 'Ext.panel.Panel',

    layout: 'border',

    title : 'entity',

    items: [
        {
            region           : 'west',
            split            : true,
            collapsible      : true,
            animCollapse     : true,
            margins          : '0 0 0 0',
            width            : 200,
            hideCollapseTool : true,
            header           : false,
            //xtype            : 'cwc_transaction_tags',
        },
        {
            region: 'center',
            //xtype: 'cwc_transaction_list'
        }
    ]
});