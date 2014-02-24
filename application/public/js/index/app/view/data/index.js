Ext.define('cwc.view.data.index' ,{
    extend: 'Ext.panel.Panel',

    layout: 'border',

    title : 'entity',

    initComponent: function(arguments) {
        var entity = this.myparams.entity;
        this.setTitle(entity.name);

        this.items = [
            {
                region           : 'west',
                split            : true,
                collapsible      : true,
                animCollapse     : true,
                margins          : '0 0 0 0',
                width            : 200,
                hideCollapseTool : true,
                header           : false,
                xtype            : 'cwc_data_tags',
                entity           : entity
            },
            {
                region : 'center',
                xtype  : 'cwc_data_list',
                entity : entity
            }
        ];

        this.callParent(arguments);
    }
});

Ext.define('cwc.view.data.tags.grid' ,{
    extend: 'Ext.grid.Panel',
    alias: 'widget.cwc_data_tags',
    title: 'Теги',
    //store: {type: 'tag', autoLoad: true},
    initComponent: function() {
        var entity = this.entity;
        this.columns = [
            {text: 'Теги',  dataIndex: 'name',  flex: 1, filter: {xtype: 'textfield'}},
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

        Ext.apply(this, {
            store: new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    api: {
                        read: '/tag/index',
                    },
                    reader: {
                        type: 'json',
                        root: 'items',
                        successProperty: 'success',
                        totalProperty: 'total'
                    },
                    extraParams: {
                        entity_id: entity.id
                    }
                },
                remoteFilter : true,
                fields        : [
                    {name: 'id'},
                    {name: 'name'},
                ],
                autoLoad     : true
            })
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


Ext.define('cwc.view.data.index.grid' ,{
    extend: 'Ext.grid.Panel',
    alias: 'widget.cwc_data_list',

    //title: 'Транзакции',

    //store: 'transaction',//{type: 'transaction', autoLoad: false},

    initComponent: function(arguments) {
        //console.log(this.entity);
        var entity = this.entity;

        var types = {
            '1' : {
            },
            '2' : {
            },
            '3' : {
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
            }
        };
        
        //this.store = {type: 'transaction', autoLoad: true};

        var fields = [{name: 'id'}];

        var columns = [
            {
                xtype        : 'actioncolumn',
                width        : 60,
                menuDisabled : true,
                items        : [
                    {
                        icon    : '/img/extjs/icons/edit.png',
                        tooltip : 'Редактировать',
                        scope   : this,
                        handler : function(grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            this.fireEvent('cwc_data_edit', this, rec, entity);
                        }
                    }
                ]
            },
            {header: 'id',  dataIndex: 'id',  flex: 1, filter: {xtype: 'textfield'}},
        ];

        Ext.each(entity.fields, function(field){
            columns[columns.length] = Ext.apply(types[field['field_type']], {text: field.name, dataIndex: field.field_name, flex: 1});
            fields[fields.length]   = {name: field.field_name};
        },this);

        if(entity.tagged) {
            columns[columns.length] = {text: 'Теги',  dataIndex: 'tags',  flex: 1};
            fields[fields.length]   = {name: 'tags'};
        }
            /*{header: 'Дата',  dataIndex: 'date',  flex: 1, filter: {xtype: 'textfield'}},
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
            {header: 'Теги',  dataIndex: 'tags',  flex: 1, filter: {xtype: 'textfield'}},*/
        columns[columns.length] = {
            xtype        :'actioncolumn',
            width        : 30,
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
                                url     : '/data/delete',
                                method  : 'POST',
                                waitMsg : 'Отправка данных',
                                scope   : this,
                                params  : {
                                    id        : rec.get('id'),
                                    entity_id : entity.id
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

        Ext.apply(this, {
            store: new Ext.data.JsonStore({
                proxy: {
                    type: 'ajax',
                    api: {
                        read: '/data/index',
                    },
                    reader: {
                        type: 'json',
                        root: 'items',
                        successProperty: 'success',
                        totalProperty: 'total'
                    },
                    extraParams: {
                        entity_id: entity.id
                    }
                },
                remoteFilter : true,
                fields       : fields,
                autoLoad     : true
            }),
            columns: columns
        });

        var gridheaderfilters = new Ext.ux.grid.plugin.HeaderFilters({ 
            stateful              : false, 
            ensureFilteredVisible : false,
            reloadOnChange        : false
        });

        Ext.apply(this, {
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
            },
            {
                xtype: 'toolbar',
                items : [
                    '->',
                    {
                        text    : 'Добавить',
                        scope   : this,
                        handler : function() {
                            this.fireEvent('cwc_data_add', entity);
                        }
                    }
                ]
            }]
        });

        this.callParent(arguments);
    },
    /*onRender: function() {
        this.callParent(arguments);console.log(this);
        this.store.pageSize = page_size-5;
        this.store.getProxy().extraParams = this.myparams;
        this.store.load();
    }*/
});