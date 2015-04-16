var timeout = null;
var timeout2 = null;
function doLoadUp(step) {
	if(timeout)
		clearTimeout(timeout);
	if(step > 0)
		timeout = setTimeout(doLoad, step);
	else
		timeout = setTimeout(doLoad, 100);
}
function doHide() {
	timeout2 = setTimeout("document.getElementById('variants').style.display='none'",1000);
}

function doGetNet() {
	document.getElementById('getnet_size').style.visibility = "hidden";
	document.getElementById('getnet_button').disabled = true;
	
	var query = '' + document.getElementById('getnet_size').value; 

    $.getJSON('/index_lite.php?module=routers&action=n_acquire_as&query=' + encodeURI(query))
        .done(function(data){
            document.getElementById('getnet_button').disabled = false;
            document.getElementById('getnet_size').style.visibility = "";
            if (data && data.data) {
                document.getElementById('net').value = data.data;
            }
        });
}


function doLoad() { 
	var query = '' + document.getElementById('searchfield').value; 
    $.getJSON('/index_lite.php?module=clients&action=search_as&query=' + encodeURI(query))
        .done(function(data){
            if (data && data.data) {
                document.getElementById('variants').innerHTML = data.data;
                document.getElementById('variants').style.display="";
            } else document.getElementById('variants').style.display='none';
        })
} 

function openNavigationBlock(id){
    var openedBlocks = JSON.parse(localStorage.getItem('navigation-opened-blocks') || '{}');
    $('#' + id).addClass('opened');
    openedBlocks[id] = true;
    localStorage.setItem('navigation-opened-blocks', JSON.stringify(openedBlocks));
}

function closeNavigationBlock(id){
    var openedBlocks = JSON.parse(localStorage.getItem('navigation-opened-blocks') || '{}');
    $('#' + id).removeClass('opened');
    delete openedBlocks[id];
    localStorage.setItem('navigation-opened-blocks', JSON.stringify(openedBlocks));
}

function toggleNavigationBlock(id){
    if ($('#' + id).hasClass('opened')) {
        closeNavigationBlock(id);
    } else {
        openNavigationBlock(id);
    }
}

function initNavigationBlocks(){
    var openedBlocks = JSON.parse(localStorage.getItem('navigation-opened-blocks') || '{}');
    for(var blockId in openedBlocks) {
        openNavigationBlock(blockId);
    }
}


function toggle2(obj){
	if (!obj.style) obj = document.getElementById(obj);
	if (!obj) return;
	if (obj.style.display=='none'){
		obj.style.display = '';
	} else {
		obj.style.display = 'none';	
	}
}

function menu_item(element,flag){
	if (flag){
		element.style.backgroundColor="#FFA3A3";
	}else {
		element.style.backgroundColor="#FFFFD8";
	};
}


function options_waiting(id,add_first) {
	var obj=document.getElementById(id);
	for (var i=obj.childNodes.length-1;i>=0;i--) {
		obj.removeChild(obj.childNodes[i]);
	}
	if (add_first) {
		var opt = document.createElement("OPTION");
		opt.innerHTML = "загрузка...";
		opt.value = "";
		obj.appendChild(opt);
	}
}

function options_update(id,data) {
	var obj=document.getElementById(id);
	if (obj.childNodes.length) obj.removeChild(obj.childNodes[0]);
	for (var i in data) {
		if(arguments[2] && !data[i])
			continue
		var opt = document.createElement("OPTION");
		opt.innerHTML = data[i];
		opt.value = i;
		if (opt.value==obj.getAttribute('tag')) opt.selected=1;
		obj.appendChild(opt);
	}
}

function options_select(id,value) {
	var obj=document.getElementById(id);
    for(i =0;i< obj.options.length; i++)
    {
        if(obj.options[i].value == value)
        {
            obj.selectedIndex = i;
            return;
        }
    }
}

function form_ip_ports_tarif(){
	val=document.getElementById('t_tarif_type').value;
	val+=document.getElementById('t_tarif_status').value;
	document.getElementById('t_id_tarifIP').style.display=(val=='IP'?'':'none');
	document.getElementById('t_id_tarifIS').style.display=(val=='IS'?'':'none');
	document.getElementById('t_id_tarifIA').style.display=(val=='IA'?'':'none');
	document.getElementById('t_id_tarifCP').style.display=(val=='CP'?'':'none');
	document.getElementById('t_id_tarifCS').style.display=(val=='CS'?'':'none');
	document.getElementById('t_id_tarifCA').style.display=(val=='CA'?'':'none');
	document.getElementById('t_id_tarifVP').style.display=(val=='VP'?'':'none');
	document.getElementById('t_id_tarifVS').style.display=(val=='VS'?'':'none');
	document.getElementById('t_id_tarifVA').style.display=(val=='VA'?'':'none');
	document.getElementById('t_id_tarifISu').style.display=(val=='ISu'?'':'none');
	document.getElementById('t_id_tarifISs').style.display=(val=='ISs'?'':'none');
	document.getElementById('t_id_tarifISc').style.display=(val=='ISc'?'':'none');
}

function form_ip_ports_hide(is_first){
	var val=document.getElementById('port_type').value;
	if (!is_first) document.getElementById('port').value=(val=='adsl'||val=='adsl_cards'||val=='adsl_connect'||val=='adsl_karta'||val=='adsl_rabota'||val=='adsl_terminal'||val=='adsl_tranzit1'?'mgts':document.getElementById('port').getAttribute('tag'));

	if (val=='adsl'||val=='adsl_cards'||val=='adsl_karta'||val=='adsl_connect'||val=='adsl_rabota'||val=='adsl_terminal'||val=='adsl_tranzit1') {
		document.getElementById('tr_node').style.display='none';
		document.getElementById('tr_phone').style.display='';
		document.getElementById('tr_port').style.display='none';
		//document.getElementById('tr_speed_contract').style.display='';
		form_ip_ports_tarif()
	} else if (val=='wimax' || val == 'yota' || val == 'GPON') {
		document.getElementById('tr_node').style.display='none';
		document.getElementById('tr_phone').style.display='none';
		document.getElementById('tr_port').style.display='none';
		document.getElementById('tr_speed_contract').style.display='none';
	} else {
		document.getElementById('tr_node').style.display='';
		document.getElementById('tr_phone').style.display='none';
		document.getElementById('tr_port').style.display='';
		document.getElementById('tr_speed_contract').style.display='none';
		form_ip_ports_get_ports();
	}
}

var usagevoip_line7800_hide = false;
function form_usagevoip_hide()
{
    var val = document.getElementById("E164").value;

    var toHide = false;

    toHide = val.substr(0,4) != "7800";

    if (toHide != usagevoip_line7800_hide)
    {
        document.getElementById("tr_line7800_id").style.display= toHide ? 'none' : '';
        usagevoip_line7800_hide = toHide;
    }
}

function form_ip_ports_get_ports() {
	var nodeval=document.getElementById('node').value;
	var porttypeval=document.getElementById('port_type').value;
	options_waiting('port',(nodeval && porttypeval));
    $.getJSON('index_lite.php?module=services&action=in_async&node='+nodeval+'&port_type='+porttypeval)
        .done(function(data){
            if (data && data) {
                options_update('port',data.ports);
            }
        });
}

function form_cpe_get_clients(first_load) { 
	var id_modelval=document.getElementById('id_model').value;
	document.getElementById('deposit_sumRUB').title='загрузка...';
	document.getElementById('deposit_sumUSD').title='загрузка...';
	options_waiting('client',id_modelval);
    $.getJSON('/index_lite.php?module=routers&action=d_async&res=client&id_model='+id_modelval+'&client='+document.getElementById('client').getAttribute('tag'))
        .done(function(data){
            if (data.depositUSD && data.depositRUB) {
                document.getElementById('deposit_sumUSD').title=data.depositUSD;
                document.getElementById('deposit_sumRUB').title=data.depositRUB;
                if (!document.getElementById('deposit_sumRUB').value) document.getElementById('deposit_sumRUB').value=data.depositRUB;
                if (!document.getElementById('deposit_sumUSD').value) document.getElementById('deposit_sumUSD').value=data.depositUSD;
            }
            if (data.data) {
                options_update('client',data.data, true);
                options_select('client', _client);
                form_cpe_get_services();
            }
        });
} 

function form_cpe_get_services(first_load) {
	var id_modelval=document.getElementById('id_model').value;
	var clientval=(first_load?document.getElementById('client').getAttribute('tag'):document.getElementById('client').value);
	options_waiting('id_service',id_modelval);
    $.getJSON('/index_lite.php?module=routers&action=d_async&res=service&id_model='+id_modelval+'&client='+clientval+'&id='+document.getElementById('id_service').getAttribute('tag'))
        .done(function(data){
            if (data && data.data) options_update('id_service',data.data);
        })
} 

function form_usage_extra_group(o) {
    var gValue = o.options[o.selectedIndex].value;

    var oTarif = document.getElementById("tarif_id");

    for(i = oTarif.options.length; i > 1 ;i--){
        oTarif.remove(i-1);
    }

    aIds = tGroup[gValue];

    var optNames = new Array();

    if(aIds){
        for(a in aIds){
            id = aIds[a];
            if(ids[id])
                optNames.push([id,ids[id]])
        }
    }

    optNames.sort(optSort);

    for(a in optNames) {
        var o = optNames[a];
        createOption(oTarif, o[0], o[1]);
    }

}

function optSort(i, ii)
{
    return i[1] == ii[1] ? 0 : (i[1] > ii[1] ? 1 : -1);
}


function createOption(oSel, id,value){
    var opt = document.createElement("OPTION");
    opt.innerHTML = value;
    opt.value = id;
    oSel.appendChild(opt);
}

function form_usage_sms_get(id)
{
    __form_get(id, 'sms');
}

function form_usage_extra_get(id) {
    __form_get(id, 'extra');
}

function __form_get(id, tarif_table) {
	if (!id) {
		if (loading) return;
		var id=document.getElementById('tarif_id').value;
	}
	if (!id) return;
	document.getElementById('async_price').value='загрузка...';

    $.getJSON('/index_lite.php?module=services&action=ex_async&tarif_table='+tarif_table+'&id='+id)
        .done(function(data){
            if (data.async_price) document.getElementById('tr_async_price').childNodes[1].innerHTML=data.async_price;
            if (data.async_period) document.getElementById('tr_async_period').childNodes[1].innerHTML=data.async_period;
            if (data.param_name) {
                document.getElementById('tr_param_value').childNodes[0].innerHTML=data.param_name;
                document.getElementById('tr_param_value').style.display='';
            } else document.getElementById('tr_param_value').style.display='none';
            if (data.is_countable && data.is_countable==1) {
                document.getElementById('tr_amount').style.display='';
            } else {
                document.getElementById('amount').value="1";
                document.getElementById('tr_amount').style.display='none';
            }
            loading = false;
        });
}

function form_cpe_load(){
//	document.getElementById('service').disabled=1;
	form_cpe_get_clients(1);
}

function showHistory(model, modelId) {
    var $dialog = $('<iframe src="'+'/history/show?model=' + model + '&model_id=' + modelId+'" title="История изменений" style="display: none;"></iframe>');

    $dialog.appendTo(document.body);

    var width = window.innerWidth - 100;
    var height = window.innerHeight - 100;
    if (width > 1200) {
        width = 1200;
    }

    $dialog.dialog(
        {
            width: width,
            height: height,
            open: function(){
                $dialog[0].style.width = '100%';
            },
            close: function(){
                $dialog.remove();
            }
        });
}