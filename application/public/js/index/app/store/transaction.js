Ext.define('cwc.store.transaction', {
    extend : 'Ext.data.Store',
    alias  : 'store.transaction',
    proxy  : {
        type: 'ajax',
        api: {
            read: '/transaction/index'
        },
        reader: {
            type: 'json',
            root: 'items',
            successProperty: 'success',
            totalProperty: 'total'
        }
    },
    //pageSize: 1,
    remoteFilter: true,
    autoLoad:true,
    fields        : [
        {name: 'id'},
        {name: 'date'},
        {name: 'sum'},
        {name: 'tags'},
        {name: 'comment'}
    ]
});