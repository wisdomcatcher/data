Ext.define('cwc.controller.base', 
{
    extend: 'Ext.app.Controller',

    redirect_to_index: function()
    {
        var hash = '';
        //if(this.id != 'subscription')
        //{
            hash = this.id + '/index';
        //}

        window.location.hash = hash;
    },

    indexAction : function(params)
    {
        ProcessView(this, this.id + '.index', params);
    },

    editAction: function(params) 
    {
        if(params.id)
        {
            var view = ProcessView(this, this.id + '.edit', params);
            this.load_edit_form(view, params.id);
            /*view.down('form').getForm().load(
            {
                url     : '/' + this.id + '/get',
                //waitMsg : 'Загрузка',
                params  : 
                {
                    id : params.id
                }
            });*/
        }
        else
        {
            this.redirect_to_index();
        }
    },

    load_edit_form: function(view, id)
    {
        view.el.mask('Загрузка');
        view.down('form').getForm().load(
        {
            url     : '/' + this.id + '/get',
            //waitMsg : 'Загрузка',
            scope   : view,
            params  : 
            {
                id : id
            },
            success : function() 
            {
                this.el.unmask();
            }
        });
    },

    open_edit_form: function(params)
    {
        var view = Ext.widget('cwc_' + this.id + '_edit', {myparams: params});
        this.load_edit_form(view, params.id);
        /*view.el.mask('Загрузка');
        view.down('form').getForm().load(
        {
            url     : '/' + this.id + '/get',
            //waitMsg : 'Загрузка',
            scope   : view,
            params  : 
            {
                id : params.id
            },
            success : function() 
            {
                this.el.unmask();
            }
        });*/
    },

    open_add_form: function(params)
    {
        Ext.widget('cwc_' + this.id + '_add');
    },

    update_db_record: function(params)
    {
        var params = params || {};
        var view   = Ext.ComponentQuery.query('cwc_' + this.id + '_edit')[0];
        var form   = Ext.ComponentQuery.query('cwc_' + this.id + '_edit form')[0];
        form       = form.getForm();

        if (form.isValid()) 
        {
            //view.el.mask('Отправка данных');
            form.submit(
            {
                url: '/' + this.id + '/update',
                waitMsg : 'Отправка данных',
                scope   : this,
                params  : 
                {
                    id : view.myparams.id,
                    cwc_ui_confirmed: params.confirmed == undefined ? 0 : params.confirmed
                },
                success: function(form, action) 
                {
                    //view.el.unmask();
                    if(!action.result.systemMessage && !action.result.redirect && action.result.confirmMessage)
                    {
                        Ext.Msg.confirm('Подтверждение', action.result.confirmMessage, function(btn){
                            if (btn == 'yes') {
                                this.update_db_record({'confirmed': 1});
                            }
                        }, this);
                    }
                    if (!action.result.systemMessage && !action.result.redirect && !action.result.confirmMessage) 
                    {
                    //if (action.result.systemMessage)
                    //{
                    //    var data = action.result.systemMessage;
                    //    infoReport(data.title, data.message, data.icon);
                    //} 
                    //else
                    //{
                        view.close();
                        this.redirect_to_index();
                        this.getStore(this.id).reload();
                    }
                }
            });
        }
    },

    add_db_record: function(params)
    {
        var params = params || {};
        var view   = Ext.ComponentQuery.query('cwc_' + this.id + '_add')[0];
        var form   = Ext.ComponentQuery.query('cwc_' + this.id + '_add form')[0];
        form       = form.getForm();

        if (form.isValid()) 
        {
            //view.el.mask('Отправка данных');
            form.submit(
            {
                url: '/' + this.id + '/add',
                waitMsg : 'Отправка данных',
                scope   : this,
                params: {
                    cwc_ui_confirmed: params.confirmed == undefined ? 0 : params.confirmed
                },
                success: function(form, action) 
                {
                    if(!action.result.systemMessage && !action.result.redirect && action.result.confirmMessage)
                    {
                        Ext.Msg.confirm('Подтверждение', action.result.confirmMessage, function(btn){
                            if (btn == 'yes') {
                                this.add_db_record({'confirmed': 1});
                            }
                        }, this);
                    }
                    if (!action.result.systemMessage && !action.result.redirect && !action.result.confirmMessage) 
                    {
                        view.close();
                        this.redirect_to_index();
                        this.getStore(this.id).reload();
                    }
                }
            });
        }
    },

    update_field: function(rec_id, field, value)
    {
        var params = {'id': rec_id};
        params[field] = value;
        Ext.Ajax.request({
            url: '/' + this.id + '/update',
            method  : 'POST',
            waitMsg : 'Отправка данных',
            scope   : this,
            params  : params,
            success: function(form, action) 
            {
                this.getStore(this.id).reload();
            }
        });
    }
});