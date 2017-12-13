Ext.Loader.setConfig({enabled: true});

Ext.Loader.setPath('Ext.ux', '/PVWEB/js/ext-lib/models/ux/');
Ext.require([
    'Ext.grid.*',
    'Ext.data.*',
    'Ext.util.*',
    'Ext.toolbar.Paging',
    'Ext.ux.PreviewPlugin',
    'Ext.ModelManager',
    'Ext.tip.QuickTipManager'
]);

var itemsPerPage = 20;

Ext.onReady(function(){
    Ext.tip.QuickTipManager.init();

	Ext.define('SubClient', {
        extend: 'Ext.data.Model',
        fields: [
            'client_id','account','name','insert_time','area_num'
        ]
		//idProperty: 'name'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.JsonStore', {
		autoLoad: false,
		//autoLoad: {start: 0, limit: itemsPerPage},
        pageSize: itemsPerPage,
        model: 'SubClient',
        remoteSort: true,

        proxy: {
            type: 'ajax',
            url: '/PVWEB/index.php/home/get_sub_account',
            reader: {
				type: 'json',
                root: 'results',
                totalProperty: 'totalCount'
            },
            // sends single sort as multi parameter
            simpleSortMode: true
        },
		sorters: [{
            property: 'account'
        }]
    });
	
	// pluggable renders
    function renderAccount(value, p, record) {
        return Ext.String.format(
            '<b><a href="/PVWEB/index.php/home/sub_account/{0}">{1}</a></b>',
            record.data.client_id, value
        );
    }	
    var pluginExpanded = true;
	
	var grid = Ext.create('Ext.grid.Panel', {
        width: 700,
        height: 400,
        store: store,
        disableSelection: true,
        loadMask: true,
		
        // grid columns
        columns:[
		Ext.create('Ext.grid.RowNumberer',{width:45}),
		{
            id: 'name',
			text: "Sub Account",
            dataIndex: 'account',
            flex: 1,
			renderer: renderAccount
        },{
            text: "Name",
            dataIndex: 'name',
            width: 200
        },{
            text: "Area Num",
            dataIndex: 'area_num',
            width: 100
        },{
            text: "Created Time",
            dataIndex: 'insert_time',
            width: 150
        }],
        // paging bar on the bottom
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true,
            displayMsg: 'Sub Account: {0} - {1} of {2}',
            emptyMsg: "No Sub Account to display"
        }),
		
        renderTo: 'account-grid'
    });
	

    // trigger the data store load
    store.loadPage(1);
	
	
});