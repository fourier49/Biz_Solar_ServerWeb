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

	Ext.define('JunctionBox', {
        extend: 'Ext.data.Model',
        fields: [
            'jb_mac','voltage','current','temp','power','update_time','pos'
        ]
		//idProperty: 'name'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.JsonStore', {
		autoLoad: false,
		//autoLoad: {start: 0, limit: itemsPerPage},
        pageSize: itemsPerPage,
        model: 'JunctionBox',
        remoteSort: true,

        proxy: {
            type: 'ajax',
            url: '/PVWEB/index.php/home/get_jb',
            reader: {
				type: 'json',
                root: 'results',
                totalProperty: 'totalCount'
            },
            // sends single sort as multi parameter
            simpleSortMode: true
        },
		sorters: [{
            property: 'jb_mac'
        }]
    });
	
	// pluggable renders
	
	function renderName(value, p, record) {
        return Ext.String.format(
			'<a href="/PVWEB/index.php/home/jb_detail/0/{0}">DC{1}</a>',
            value, value
        );
    }
	
	function renderV(value, p, record) {
        return Ext.String.format(
            '{0} V', value
        );
    }
	
	function renderA(value, p, record) {
        return Ext.String.format(
            '{0} A', value
        );
    }
	
	function renderT(value, p, record) {
        return Ext.String.format(
            '{0} ℃', value
        );
    }
	
	function renderPower(value, p, record) {
        return Ext.String.format(
            '{0} W', value
        );
    }
	
    var pluginExpanded = true;
	
	var grid = Ext.create('Ext.grid.Panel', {
        width: 500,
        height: 460,
        //title: 'Array Manager',
        store: store,
        disableSelection: true,
        loadMask: true,
		
        // grid columns
        columns:[
		Ext.create('Ext.grid.RowNumberer',{width:35}),
		{
            id: 'name',
			text: "Mac",
            dataIndex: 'jb_mac',
            flex: 1,
			renderer: renderName
        },{
            text: "Pos",
            dataIndex: 'pos',
            width: 40
        },{
            text: "Voltage",
            dataIndex: 'voltage',
            width: 60,
			renderer: renderV
        },{
            text: "Current",
            dataIndex: 'current',
            width: 60,
			renderer: renderA
        },{
            text: "Temperature",
            dataIndex: 'temp',
            width: 60,
			renderer: renderT
        },{
            text: "Power",
            dataIndex: 'power',
            width: 100,
			renderer: renderPower
        },{
            text: "Updated Time",
            dataIndex: 'update_time',
            width: 150,
			hidden: true
        }],
        // paging bar on the bottom
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true,
            displayMsg: 'Junction Box: {0} - {1} of {2}',
            emptyMsg: "No Junction Box to display"/*,
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
		
        renderTo: 'jb-grid'
    });
	

    // trigger the data store load
    store.loadPage(1);
	
	
});