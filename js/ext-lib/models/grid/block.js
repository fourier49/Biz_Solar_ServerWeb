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

	Ext.define('Block', {
        extend: 'Ext.data.Model',
        fields: [
            'block_id','jb_num','block_power','state','update_time','block_name'
        ]
		//idProperty: 'name'
    });

    // create the Data Store
    var store = Ext.create('Ext.data.JsonStore', {
		autoLoad: false,
		//autoLoad: {start: 0, limit: itemsPerPage},
        pageSize: itemsPerPage,
        model: 'Block',
        remoteSort: true,

        proxy: {
            type: 'ajax',
            url: '/PVWEB/index.php/home/get_block',
            reader: {
				type: 'json',
                root: 'results',
                totalProperty: 'totalCount'
            },
            // sends single sort as multi parameter
            simpleSortMode: true
        },
		sorters: [{
            property: 'block_name'
        }]
    });
	
	// pluggable renders
    function renderName(value, p, record) {
        return Ext.String.format(
            '<b><a href="/PVWEB/index.php/home/jb/0/{0}">{1}</a></b>',
            record.data.block_id,
			value
        );
    }
	
	function renderPower(value, p, record) {
		if(value > 0)
        return Ext.String.format(
            '{0} KW', Math.round(value)
        );
    }
	
	function renderState(value, p, record) {
		if(value < 1)
			return Ext.String.format('Offline');
		else if(value < 2)
			return Ext.String.format('-');
		else
			return Ext.String.format('<font color="red">Exception</font>');
    }
	
    var pluginExpanded = true;
	
	var grid = Ext.create('Ext.grid.Panel', {
        width: 500,
        height: 560,
        //title: 'Array Manager',
        store: store,
        disableSelection: true,
        loadMask: true,
		
        // grid columns
        columns:[
		Ext.create('Ext.grid.RowNumberer',{width:45}),
		{
            id: 'block_name',
			text: "Name",
            dataIndex: 'block_name',
            flex: 1,
			renderer: renderName
        },{
            id: 'jb_num',
			text: "J-Box Num",
            dataIndex: 'jb_num',
            width: 80
        },{
            text: "Power",
            dataIndex: 'block_power',
            width: 100,
			renderer: renderPower
        },{
            text: "State",
            dataIndex: 'state',
            width: 70,
			renderer: renderState
        },{
            text: "Update Time",
            dataIndex: 'update_time',
            width: 140
        }],
        // paging bar on the bottom
        bbar: Ext.create('Ext.PagingToolbar', {
            store: store,
            displayInfo: true,
            displayMsg: 'Block: {0} - {1} of {2}',
            emptyMsg: "No Block to display"/*,
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
		
        renderTo: 'block-grid'
    });
	

    // trigger the data store load
    store.loadPage(1);
	
	
});