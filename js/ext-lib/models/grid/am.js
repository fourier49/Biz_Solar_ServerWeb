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

var itemsPerPage = 50;

Ext.onReady(function(){
    Ext.tip.QuickTipManager.init();

	Ext.define('ArrayManager', {
        extend: 'Ext.data.Model',
        fields: [
            'am_id','mac','ip','port','period','register_time','am_power'
        ]
		//idProperty: 'name'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.JsonStore', {
		autoLoad: false,
		//autoLoad: {start: 0, limit: itemsPerPage},
        pageSize: itemsPerPage,
        model: 'ArrayManager',
        remoteSort: true,

        proxy: {
            type: 'ajax',
            url: '/PVWEB/index.php/home/get_am',
            reader: {
				type: 'json',
                root: 'results',
                totalProperty: 'totalCount'
            },
            // sends single sort as multi parameter
            simpleSortMode: true
        },
		sorters: [{
            property: 'mac'
        }]
    });
	
	// pluggable renders
    function renderName(value, p, record) {
        return Ext.String.format(
            '<b><a href="/PVWEB/index.php/home/block/0/{0}">A8{1}</a></b>',
            record.data.am_id,
			value
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
        width: 500,
        height: 620,
        //title: 'Array Manager',
        store: store,
        disableSelection: true,
        loadMask: true,
		
        // grid columns
        columns:[
		Ext.create('Ext.grid.RowNumberer',{width:45}),
		{
            id: 'name',
			text: "Mac",
            dataIndex: 'mac',
            flex: 1,
			renderer: renderName
        },{
            text: "ip",
            dataIndex: 'ip',
            width: 120
        },{
            text: "port",
            dataIndex: 'port',
            width: 50
        },{
            text: "Power",
            dataIndex: 'am_power',
            width: 70,
			renderer: renderPower
        },{
            text: "period",
            dataIndex: 'period',
            width: 50,
			hidden: true
        },{
            text: "Registered Time",
            dataIndex: 'register_time',
            width: 100,
			hidden: true
        }],
        // paging bar on the bottom
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true,
            displayMsg: 'Array Manager: {0} - {1} of {2}',
            emptyMsg: "No Array Manager to display"/*,
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
		
        renderTo: 'am-grid'
    });
	

    // trigger the data store load
    store.loadPage(1);
	
	
});