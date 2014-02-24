Ext.define('cwc.view.transaction.edit', {
    extend: 'Ext.window.Window',
    //extend: 'Ext.panel.Panel',
    alias: 'widget.cwc_transaction_edit',

    title: 'Редактирование транзакции',
    autoShow: true,
    width: 700,
    //autoScroll: true,
    //height: 500,
    //layout:'fit',

    initComponent: function() {
        this.items = [
            {
                xtype: 'form',
                items: 
                [
                    {
                        xtype: 'tabpanel',
                        items: 
                        [
                            {
                                title     : 'свойства',
                                padding   : 5,
                                layout    : 'form',
                                border    : 0,
                                bodyStyle : 'border: 0;',
                                items     : [
                                    {
                                        xtype      : 'datefield',
                                        name       : 'date',
                                        fieldLabel : 'Дата',
                                        format     : 'Y-m-d',
                                        value      : Ext.Date.format(new Date(), 'Y-m-d')
                                    },
                                    {
                                        xtype      : 'textfield',
                                        name       : 'sum',
                                        fieldLabel : 'Сумма',
                                        allowBlank : false,
                                        vtype      : 'Numeric'
                                    },
                                    {
                                        xtype      : 'textfield',
                                        name       : 'comment',
                                        fieldLabel : 'Комментарий'
                                    },
                                    {
                                        xtype      : 'textfield',
                                        name       : 'tags',
                                        fieldLabel : 'Теги',
                                        allowBlank : false
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        ];

        this.buttons = [
            {
                text   : 'Сохранить',
                action : 'save'
            },
            {
                text    : 'Закрыть',
                action  : 'close',
                scope   : this,
                handler : this.close
            }
        ];

        this.callParent(arguments);
    }
});