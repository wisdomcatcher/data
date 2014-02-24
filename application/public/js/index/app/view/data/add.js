Ext.define('cwc.view.data.add', {
    extend: 'Ext.window.Window',
    //extend: 'Ext.panel.Panel',
    alias: 'widget.cwc_data_add',

    title: 'Добавление',
    autoShow: true,
    width: 700,
    //autoScroll: true,
    //height: 500,
    //layout:'fit',

    initComponent: function() {
        var types  = cwcApp.field_types;
        var entity = this.entity;
        var fields = [{xtype:'hidden', name: 'entity_id', value: entity.id}];
        Ext.each(entity.fields, function(field){
            var f = Ext.apply(types[field['field_type']], {
                name: field['field_name'],
                fieldLabel: field['name']
            });
            fields[fields.length] = f;
        });
        if(entity.tagged) {
            fields[fields.length] = {
                xtype      : 'textfield',
                name       : 'tags',
                fieldLabel : 'Теги',
                allowBlank : false
            }
        }
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
                                items     : fields/*[
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
                                ]*/
                            }
                        ]
                    }
                ]
            }
        ];

        this.buttons = [
            {
                text: 'Добавить',
                action: 'add'
            },
            {
                text: 'Закрыть',
                action: 'close',
                scope: this,
                handler: this.close
            }
        ];

        this.callParent(arguments);
    }
});