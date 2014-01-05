Ext.define('cwc.store.tag', {
    extend : 'Ext.data.Store',
    alias  : 'store.tag',
    proxy  : {
        type: 'ajax',
        api: {
            read: '/tag/index'
        },
        reader: {
            type: 'json',
            root: 'items',
            successProperty: 'success',
            totalProperty: 'total'
        }
    },
    remoteFilter: true,
    fields        : [
        {name: 'id'},
        {name: 'name'},
    ]
});