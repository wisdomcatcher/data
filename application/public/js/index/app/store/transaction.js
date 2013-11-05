Ext.define('cwc.store.transaction', {
    extend : 'Ext.data.Store',
    alias  : 'store.transaction',
    proxy  : {
        type: 'ajax',
        api: {
            read: '/application/transaction/index'
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
        {name: 'date'},
        {name: 'sum'},
        {name: 'tags'},
        {name: 'comment'}
    ]
});