var cwcApp;

Ext.onReady(function()
{

Ext.Loader.setConfig({ 
    enabled: true,
    paths: {
        'Ext.ux'          : 'js/extjs/examples/ux',
        'cwc.ux'          : 'js/index/app/ux',
        'Ext.ux.exporter' : 'js/index/plugins/exporter'
    }
});

Ext.application({
    requires: [
        'Ext.container.Viewport', 
        'Ext.History',
        //'Ext.ux.grid.FiltersFeature',
        //'Ext.ux.exporter.Exporter',
        //'cwc.ux.JsonCombo',
        'cwc.ux.PageSize',
        //'cwc.ux.subscription_magazines_editor',
        //'cwc.ux.YearCombo'
    ],
    name: 'cwc',

    appFolder: 'js/index/app',

    controllers: [
        'data',
        'entity'
    ],

    launch: function() {
        cwcApp = cwc.app;
        Ext.History.init();
        Ext.create('Ext.container.Viewport', {
            layout: 'border',
            items: [
                {
                    region  : 'north',
                    xtype   : 'panel',
                    //baseCls : 'x-plain',
                    tbar    : topMenu
                },
                {
                    id     : 'NXContentPanel', 
                    region : 'center',
                    layout : 'fit',
                    listeners: {
                        boxready:function()
                        {
                            page_size = Math.floor(this.getHeight()/23);
                        }
                    }
                }
            ]
        });

        var componentToken = Ext.History.getToken();

        ProcessLocation(this, componentToken);

        Ext.History.on('change', function(componentToken, opts) 
        {
            ProcessLocation(this, componentToken, opts);
        }, this);

        Ext.Ajax.on('requestcomplete', function(conn, response, options) 
        {
            var data = Ext.decode(response.responseText);
            if (data.redirect)
            {
                window.location = data.redirect;
            }
            else if (data.systemMessage) 
            {
                var data = data.systemMessage;
                infoReport(data.title, data.message, data.icon);
            }
        });
    },
    initComponent: function(componentToken) 
    {
        var currentToken = Ext.History.getToken();
        if(currentToken == componentToken) 
        {
            ProcessLocation(this, componentToken);
        } else 
        {
            Ext.History.add(componentToken);
        }
    },
    field_types: {
        '1' : {
            xtype: 'textfield',
        },
        '2' : {
            xtype  : 'datefield',
            format : 'Y-m-d',
            value  : Ext.Date.format(new Date(), 'Y-m-d')
        },
        '3' : {
            xtype      : 'textfield',
            allowBlank : false,
            vtype      : 'Numeric'
        }
    }
});

});

