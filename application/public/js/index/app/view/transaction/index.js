Ext.define('cwc.view.transaction.index' ,{
    extend: 'Ext.grid.Panel',
    alias: 'widget.cwc_transaction_list',

    title: 'Транзакции',

    store: 'transaction',

    initComponent: function() {

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
            {header: 'Сумма',  dataIndex: 'sum',  flex: 1, filter: {xtype: 'textfield'}},
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
            } 
        });

        Ext.apply(this, {
            dockedItems: [{
                xtype: 'pagingtoolbar',
                store: this.store,
                dock: 'bottom',
                displayInfo: true,
                displayMsg  : 'Показано {0} - {1} из {2}',
                emptyMsg    : 'Нет данных'
            },
            {
                xtype: 'toolbar',
                items : [
                    {
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
                    },
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
    }
});