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
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() { 
		if (req.readyState == 4) {
			document.getElementById('getnet_button').disabled = false;
			document.getElementById('getnet_size').style.visibility = "";
			if (req.responseJS && req.responseJS.data) { 
				document.getElementById('net').value = req.responseJS.data;
			}
		} 
	} 
	req.caching = false; 
	req.open('POST', '?module=routers&action=n_acquire_as', true); 
	v={};
	v['query']=query;
	req.send(v);
}


function doLoad() { 
	var query = '' + document.getElementById('searchfield').value; 
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() { 
		if (req.readyState == 4) {
			if (req.responseJS && req.responseJS.data) { 
				document.getElementById('variants').innerHTML = req.responseJS.data;
				document.getElementById('variants').style.display="";
			} else document.getElementById('variants').style.display='none';
			//document.getElementById('variants').innerHTML = req.responseText; 
		} 
	} 
	req.caching = false; 
	req.open('POST', '?module=clients&action=search_as', true); 
	v={};
	v['query']=query;
	req.send(v);
} 
 

function toggle(obj,link,module){
	if (obj.style.display=='inline'){
		obj.style.display = 'none';
		link.innerHTML='&raquo;';
		value = '0';
	} else {
		obj.style.display = 'inline';	
		link.innerHTML='&laquo;';
		value = '1';
	}
	if (module!="") document.getElementById("toggle_frame").src=("?module=usercontrol&action=ex_toggle&panel=" + module + "&value=" + value);
}

function toggle2(obj){
	if (!obj.style) obj = document.getElementById(obj);
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
	} else if (val=='wimax' || val == 'yota') {
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

function form_newpayments_hide()
{
    var val = document.getElementById('type').value;
    if(val != "bank")
    {
		document.getElementById('tr_bank').style.display='none';
		document.getElementById('bank').selectedIndex =1;
    }else{
		document.getElementById('tr_bank').style.display='';
    }

}

function form_ip_ports_get_ports() {
	var nodeval=document.getElementById('node').value;
	var porttypeval=document.getElementById('port_type').value;
	options_waiting('port',(nodeval && porttypeval));
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() {
		if (req.readyState == 4 && req.responseJS && req.responseJS.ports) options_update('port',req.responseJS.ports);
		if (req.responseText) document.getElementById('div_errors').innerHTML+=req.responseText;
	} 
	req.caching = false; 
	req.open('GET', PATH_TO_ROOT+'index_lite.php?module=services&action=in_async&node='+nodeval+'&port_type='+porttypeval, true); 
	req.send();			
}

function form_cpe_get_clients(first_load) { 
	var id_modelval=document.getElementById('id_model').value;
	document.getElementById('deposit_sumRUR').title='загрузка...';
	document.getElementById('deposit_sumUSD').title='загрузка...';
	options_waiting('client',id_modelval);
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() {
		if (req.readyState == 4 && req.responseJS) {
			if (req.responseJS.depositUSD && req.responseJS.depositRUR) {
				document.getElementById('deposit_sumUSD').title=req.responseJS.depositUSD;
				document.getElementById('deposit_sumRUR').title=req.responseJS.depositRUR;
				if (!document.getElementById('deposit_sumRUR').value) document.getElementById('deposit_sumRUR').value=req.responseJS.depositRUR;
				if (!document.getElementById('deposit_sumUSD').value) document.getElementById('deposit_sumUSD').value=req.responseJS.depositUSD;
			}
			if (req.responseJS.data) {
				options_update('client',req.responseJS.data, true);
                options_select('client', _client);
				form_cpe_get_services();
			}
			if (req.responseText) document.getElementById('div_errors').innerHTML+=req.responseText;
		}
	} 
	req.caching = false;
	req.open('GET', PATH_TO_ROOT+'index_lite.php?module=routers&action=d_async&res=client&id_model='+id_modelval+'&client='+document.getElementById('client').getAttribute('tag'), true); 
	req.send();
} 

function form_cpe_get_services(first_load) {
	var id_modelval=document.getElementById('id_model').value;
	var clientval=(first_load?document.getElementById('client').getAttribute('tag'):document.getElementById('client').value);
	options_waiting('id_service',id_modelval);
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() {
		if (req.readyState == 4 && req.responseJS && req.responseJS.data) options_update('id_service',req.responseJS.data);
		if (req.responseText) document.getElementById('div_errors').innerHTML+=req.responseText;
	}
	req.caching = false; 
	req.open('GET', PATH_TO_ROOT+'index_lite.php?module=routers&action=d_async&res=service&id_model='+id_modelval+'&client='+clientval+'&id='+document.getElementById('id_service').getAttribute('tag'), true);
	req.send();
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

function form_usage_extra_get(id) {
	if (!id) {
		if (loading) return;
		var id=document.getElementById('tarif_id').value;
	}
	if (!id) return;
	document.getElementById('async_price').value='загрузка...';
	var req = new Subsys_JsHttpRequest_Js();
	req.onreadystatechange = function() {
		if (req.readyState == 4 && req.responseJS) {
			if (req.responseJS.async_price) document.getElementById('tr_async_price').childNodes[1].innerHTML=req.responseJS.async_price;
			if (req.responseJS.async_period) document.getElementById('tr_async_period').childNodes[1].innerHTML=req.responseJS.async_period;
			if (req.responseJS.param_name) {
				document.getElementById('tr_param_value').childNodes[0].innerHTML=req.responseJS.param_name;
				document.getElementById('tr_param_value').style.display='';
			} else document.getElementById('tr_param_value').style.display='none';
			if (req.responseJS.is_countable && req.responseJS.is_countable==1) {
				document.getElementById('tr_amount').style.display='';
			} else {
				document.getElementById('amount').value="1";
				document.getElementById('tr_amount').style.display='none';
			}
			loading = false;
		}
		if (req.responseText) document.getElementById('div_errors').innerHTML+=req.responseText;
	}
	req.caching = false; 
	req.open('GET', PATH_TO_ROOT+'index_lite.php?module=services&action=ex_async&id='+id, true);
	req.send();
} 

function form_cpe_load(){
//	document.getElementById('service').disabled=1;
	form_cpe_get_clients(1);
}
