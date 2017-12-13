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

var itemsPerPage = 15;

Ext.onReady(function(){
    Ext.tip.QuickTipManager.init();

	Ext.define('Area', {
        extend: 'Ext.data.Model',
        fields: [
            'area_id','name','country','address','insert_time','area_power'
			//{name: 'insert_time', type: 'date', dateFormat: 'timestamp'}
        ]
		//idProperty: 'name'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.JsonStore', {
		autoLoad: false,
		//autoLoad: {start: 0, limit: itemsPerPage},
        pageSize: itemsPerPage,
        model: 'Area',
        remoteSort: true,

        proxy: {
            type: 'ajax',
            url: '/PVWEB/index.php/home/get_area',
            reader: {
				type: 'json',
                root: 'results',
                totalProperty: 'totalCount'
            },
            // sends single sort as multi parameter
            simpleSortMode: true
        },
		sorters: [{
            property: 'name'
        }]
    });
	
	// pluggable renders
    function renderName(value, p, record) {
        return Ext.String.format(
            '<b><a href="/PVWEB/home/am/0/{0}">{1}</a></b>',
            record.data.area_id, value
        );
    }
	
	function renderEdit(value, p, record) {
        return Ext.String.format(
            '<b><a href="/PVWEB/home/area/e/{0}"><img src="/PVWEB/images/button/edit.png"/></a></b>',
            record.data.area_id
        );
    }
	
	function renderPower(value, p, record) {
		if(value > 0)
        return Ext.String.format(
            '{0} KW', Math.round(value)
        );
    }
	
    var pluginExpanded = true;
	
	var grid = Ext.create('Ext.grid.Panel', {
        width: 570,
        height: 500,
        //title: 'Area List',
        store: store,
        disableSelection: true,
        loadMask: true,
		
        // grid columns
        columns:[
		Ext.create('Ext.grid.RowNumberer',{width:45}),
		{
            id: 'name',
			text: "Name",
            dataIndex: 'name',
            flex: 1,
			renderer: renderName
        },{
            text: "Country",
            dataIndex: 'country',
            width: 100
        },{
            text: "Address",
            dataIndex: 'address',
            width: 250
        },{
            text: "Power",
            dataIndex: 'area_power',
            width: 80,
			renderer: renderPower
        },{
            text: "Created Time",
            dataIndex: 'insert_time',
            width: 150,
			hidden: true
        },{
            text: "Edit",
            width: 50,
			renderer: renderEdit,
			sortable: false
        }],
        // paging bar on the bottom
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true,
            displayMsg: 'Area: {0} - {1} of {2}',
            emptyMsg: "No Area to display"/*,
            items:[
                '-', {
                text: 'Show Preview',
                pressed: pluginExpanded,
                enableToggle: true,
                toggleHandler: function(btn, pressed) {
                    var preview = Ext.getCmp('gv').getPlugin('preview');
                    preview.toggleExpanded(pressed);
                }
            }]*/
        }),
		
        renderTo: 'area-grid'
    });
	

    // trigger the data store load
    store.loadPage(1);
	
	
});