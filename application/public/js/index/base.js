/**
 * Открывает окно с сообщением системы
 * @param string infoName
 * @param string infoMessage
 * @param string infoType (error/info/question/warning)
 */
var infoReport = function(infoName, infoMessage, infoType) {
    Ext.Msg.show({
        title   : 'Сообщение системы',
        msg     : '<b>' + infoName + '</b><br/>' + infoMessage,
        buttons : Ext.Msg.OK,
        icon    : Ext.Msg.ERROR//,
        //iconCls : 'settings-ico'
    });
    throw new Error(infoName + " " + infoMessage); //die
}

var page_size = 25;
//Ext.Ajax.timeout = 300000;
//console.log(nx.getViewportHeight());

Ext.apply(Ext.form.field.VTypes, {
    Numeric     :  function(v) {
        return /^[0-9\.]+$/.test(v);
    },
    NumericText : 'Должно быть числом',
    NumericMask : /[\d\.]/i,
    alpharu     :  function(v) {
        return /^[a-zа-яё]+$/i.test(v);
    },
    alpharuText : 'Только буквы',
    alpharuMask : /[a-zа-яё]/i
});

var ProcessLocation = function(app, token, opts) 
{
    var currentToken = Ext.History.getToken();
    //if(currentToke != token)
    //{
    //  Ext.History.add(componentToken);
    //}
    var controller = 'data';
    var action     = 'index';

    var segments = token ? token.split('/') : {};

    if(segments[0]) {
        controller = segments[0];
    }

    if(segments[1]) {
        action = segments[1];
    }

    var param_id = '';
    var params = {opts: opts};
    if(segments[2])
    {
        for(i = 2; i < segments.length; i++)
        {
            if(i%2==1)
            {
                var cur_name  = segments[i-1];
                var cur_value = segments[i];
                if(cur_name == 'id') {
                    param_id = cur_value;
                }
                params[cur_name] = cur_value;
            }
        }
    }
    if(controller == 'data' && action == 'index' && param_id == '') {
        params['id'] = cwc_initial_entity_id; 
    }

    action = action + 'Action';

    app.getController(controller)[action](params);
}

var ProcessView = function(controller, view, params)
{
    var contentPanel = Ext.ComponentQuery.query('#NXContentPanel');
    var view = controller.getView(view).create({myparams: params});
    //controller.mainview = view;
    //view.controller = controller;
    if(view.store && (view.store.autoLoad==undefined || view.store.autoLoad)) 
    {
        view.store.pageSize = page_size;
        view.store.getProxy().extraParams = params;
        view.store.load();
    }
    contentPanel[0].removeAll();
    contentPanel[0].add(view);
    return view;
}

var submitFakeForm = function (url, data, filename) 
{
    var form = Ext.get('fakeForm');
    form.set({
        action : url
    });
    if(data!=undefined)
    {
        form.set({
            method : 'post'
        }).first().set({
            value : data
        }).next().set({
            value : filename
        });
    }
    document.forms.fakeForm.submit();
    return false;
}

var convertDataToTree = function (data)
{
    var results = [];
    if(data instanceof Array || Object.prototype.toString.call(data) === '[object Array]')
    {
        for(var i = 0, len = data.length; i < len; i++)
        {
            var record   = data[i];
            var children = this.convertDataToTree(record);
            results.push({
                'text'     : i,
                'children' : children,
                //'expanded' : true,
                'value'    : '',
                'leaf'     : 0
            });
        }
    }
    else if(Object.prototype.toString.call(data) === '[object Object]')
    {
        for(var prop in data)
        {
            var value = data[prop];
            if(Object.prototype.toString.call(value) === '[object Object]' || Object.prototype.toString.call(value) === '[object Array]')
            {
                var children = this.convertDataToTree(value);
                results.push({
                    'text'     : prop,
                    'children' : children,
                    //'expanded' : true,
                    'value'    : '',
                    'leaf'     : 0
                });
            }
            else
            {
                results.push({
                    'text'  : prop,// + ': ' + value,
                    'value' : value,
                    'leaf'  : 1
                });
            }
        }
    }
    else
    {
        for(var prop in data)
        {
            var value = data[prop];
            results.push({
                'text'  : prop,// + ': ' + value,
                'value' : value,
                'leaf'  : 1
            });
        }
    }
    return results;
}