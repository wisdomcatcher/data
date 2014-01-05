Ext.define('cwc.view.transaction.index' ,{
    extend: 'Ext.panel.Panel',

    layout: 'border',

    title : 'Транзакции',

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
            xtype            : 'cwc_transaction_tags',
        },
        {
            region: 'center',
            xtype: 'cwc_transaction_list'
        }
    ]
});


Ext.define('cwc.view.transaction.tags.grid' ,{
    extend: 'Ext.grid.Panel',
    alias: 'widget.cwc_transaction_tags',
    title: 'Теги',
    store: {type: 'tag', autoLoad: true},
    initComponent: function() {
        this.columns = [
            {header: 'Теги',  dataIndex: 'name',  flex: 1, filter: {xtype: 'textfield'}},
        ];

        var gridheaderfilters = new Ext.ux.grid.plugin.HeaderFilters({ 
            stateful              : false, 
            ensureFilteredVisible : false,
            reloadOnChange        : false
        });

        Ext.apply(this, {
            viewConfig : { 
                enableTextSelection: true
            },
            allowDeselect: true
        });

        this.callParent(arguments);
    },
    listeners: {
        //scope: this,
        'select' : function(grid, rec) {
            var store = this.next('grid').store;
            store.filters.removeAtKey('tag_id');
            store.filters.add('tag_id', new Ext.util.Filter({
                property: 'tag_id',
                value   : rec.get('id')
            }));
        },
        'deselect': function(grid, rec) {console.log('test2');
            var store = this.next('grid').store;
            store.filters.removeAtKey('tag_id');
        },
        'selectionchange': function() {
            var store = this.next('grid').store;
            store.reload();
        }
    }
});

Ext.define('cwc.view.transaction.index.grid' ,{
    extend: 'Ext.grid.Panel',
    alias: 'widget.cwc_transaction_list',

    //title: 'Транзакции',

    store: 'transaction',//{type: 'transaction', autoLoad: false},

    initComponent: function() {
        //this.store = {type: 'transaction', autoLoad: true};

        this.columns = [
            {
                xtype        : 'actioncolumn',
                width        : 60,
                menuDisabled : true,
                items        : [
                    {
                        icon    : '/images/extjs/icons/edit.png',
                        tooltip : 'Редактировать',
                        scope   : this,
                        handler : function(grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            this.fireEvent('cwc_transaction_edit', this, rec);
                        }
                    }
                ]
            },
            {header: 'id',  dataIndex: 'id',  flex: 1, filter: {xtype: 'textfield'}},
            {header: 'Дата',  dataIndex: 'date',  flex: 1, filter: {xtype: 'textfield'}},
            {header: 'Сумма',  dataIndex: 'sum',  flex: 1, filter: {xtype: 'textfield'}, 
                summaryType: function(rows) {
                    var sum = 0;
                    Ext.each(rows, function(row){
                        sum+=parseFloat(row.get('sum'));
                    });
                    return sum;
                },
                summaryRenderer: function(value, summaryData, dataIndex) {
                    return Ext.String.format('Итого: {0}', value); 
                }
            },
            {header: 'Комментарий',  dataIndex: 'comment',  flex: 1, filter: {xtype: 'textfield'}},
            {header: 'Теги',  dataIndex: 'tags',  flex: 1, filter: {xtype: 'textfield'}},
            {
                xtype        :'actioncolumn',
                width        :30,
                menuDisabled : true,
                items        : [{
                    icon: '/img/extjs/icons/disabled.png',
                    tooltip: 'Удалить',
                    handler: function(grid, rowIndex, colIndex) 
                    {
                        var rec = grid.getStore().getAt(rowIndex);
                        Ext.Msg.confirm('Подтверждение', 'Удалить?', function(btn){
                            if (btn == 'yes')
                            {
                                Ext.Ajax.request({
                                    url: '/application/transaction/delete',
                                    method  : 'POST',
                                    waitMsg : 'Отправка данных',
                                    scope   : this,
                                    params  : {
                                        id: rec.get('id')
                                    },
                                    success: function(form, action) 
                                    {
                                        grid.getStore().reload();
                                    }
                                });
                            }
                        }, this); 
                    }
                }]
            }
        ];

        var gridheaderfilters = new Ext.ux.grid.plugin.HeaderFilters({ 
            stateful              : false, 
            ensureFilteredVisible : false,
            reloadOnChange        : false
        });

        Ext.apply(this, {
            //plugins: [gridheaderfilters],
            viewConfig : { 
                enableTextSelection: true
            },
            features: [{
                ftype: 'summary'
            }],
        });

        Ext.apply(this, {
            dockedItems: [{
                xtype       : 'pagingtoolbar',
                store       : this.store,
                dock        : 'bottom',
                displayInfo : true,
                displayMsg  : 'Показано {0} - {1} из {2}',
                emptyMsg    : 'Нет данных',
                plugins     : [{ptype: 'pagesize'}]
                /*listeners: {
                    'render': function() {
                        this.store.reload();
                    }
                }*/
            },
            {
                xtype: 'toolbar',
                items : [
                    /*{
                        text    : 'Искать',
                        iconCls : 'search-ico',
                        scope   : this,
                        handler : function() {
                            this.applyHeaderFilters();
                        }
                    },
                    {
                        text    : 'Сброс поиска',
                        iconCls : 'clear-ico',
                        scope   : this,
                        handler : function() {
                            this.resetHeaderFilters();
                        }
                    },*/
                    '->',
                    {
                        text    : 'Добавить',
                        iconCls : 'add-ico',
                        scope   : this,
                        handler : function() {
                            this.fireEvent('cwc_transaction_add', this);
                        }
                    }
                ]
            }]
        });

        this.callParent(arguments);
    }/*,
    onRender: function(arguments) {
        this.callParent(arguments);
        //this.store.pageSize = page_size-5;
        this.store.getProxy().extraParams = this.myparams;
        this.store.load();
    }*/
});