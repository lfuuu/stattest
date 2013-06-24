var statlib = {}
statlib.modules = {}
statlib.modules.clients = {}
statlib.modules.newaccounts = {}
statlib.modules.tt = {}
statlib.modules.voip = {}

statlib.modules.clients.create = {}
statlib.modules.newaccounts.mk1cBill = {}
statlib.modules.tt.mktt = {}

statlib.modules.clients.create.findByInn = function(ev){
	var e = ev || window.event
	if(!e.target) // IE fix
		e.target = e.srcElement

	clearTimeout(statlib.modules.clients.create.findByInn.Timer)
	statlib.modules.clients.create.findByInn.Timer = setTimeout(function(event,target){
		target = target || e.target // IE fix

		jQuery.get("index_lite.php",{module:'clients',action:'rpc_findClient1c',findInn:e.target.value},function(data){
			var cl,i,el
			eval('cl = '+data)
			if(!cl){
				target.parentNode.previousSibling.firstChild.setAttribute('color', 'blue')
				return false
			}
			target.parentNode.previousSibling.firstChild.setAttribute('color', 'gray')
			for(i in cl){
				if(document.getElementsByName(i)){
					document.getElementsByName(i)[0].value = cl[i]
					if(i == 'bik')
						document.getElementsByName(i)[0].parentNode.previousSibling.firstChild.setAttribute('color', 'gray')
				}
			}
			document.getElementsByName('bank_properties')[0].value = 'р/с '+cl['pay_acc']+' '+cl['bank_name']+' '+cl['bank_city']+', к/с '+cl['corr_acc']

			el = document.getElementsByName('type')[0].firstChild
			while(true){
				if(!el)
					break
				if(el.value == cl['type']){
					el.selected = true
					break
				}
				el = el.nextSibling
			}
		})
	}, 700, e, e.target)
}
statlib.modules.clients.create.findByInn.Timer = null

statlib.modules.clients.create.findByBik = function(ev){
	var e = ev || window.event
	if(!e.target) // IE fix
		e.target = e.srcElement

	clearTimeout(statlib.modules.clients.create.findByBik.Timer)
	statlib.modules.clients.create.findByBik.Timer = setTimeout(function(event,target){
		target = target || e.target // IE fix
		jQuery.get("index_lite.php",{module:'clients',action:'rpc_findBank1c',findBik:e.target.value},function(data){
			var cl,i
			eval('cl = '+data)
			if(!cl){
				target.parentNode.previousSibling.firstChild.setAttribute('color', 'blue')
				return false
			}
			target.parentNode.previousSibling.firstChild.setAttribute('color', 'gray')
			for(i in cl){
				if(document.getElementsByName(i)){
					document.getElementsByName(i)[0].value = cl[i]
				}
			}
      var pay_acc = document.getElementsByName('pay_acc')[0].value.trim();
      if (pay_acc == '')pay_acc = '__________________';
			document.getElementsByName('bank_properties')[0].value = 'р/с '+pay_acc+' '+cl['bank_name']+' '+cl['bank_city']+', к/с '+cl['corr_acc']
		})
	}, 700, e, e.target)
}
statlib.modules.clients.create.findByBik.Timer = null

//проверка ИНН КПП и Типа клиента
statlib.modules.clients.create.checkTIK = function(){
	var inn, kpp, t
	t = document.getElementById('cl_type_org').selected
	if(!t)
		return true

	inn = document.getElementById('cl_inn').value
	kpp = document.getElementById('cl_kpp').value

	if(!inn || !kpp){
		alert('При типе организации ЮрЛицо(org) необходимо указать ИНН и КПП')
		return false
	}
	return true
}

statlib.modules.newaccounts.mk1cBill.findProduct = function(ev){
	var e = ev || window.event
	if(!e.target) // IE fix
		e.target = e.srcElement

	clearTimeout(statlib.modules.newaccounts.mk1cBill.findProduct.Timer)
	statlib.modules.newaccounts.mk1cBill.findProduct.Timer = setTimeout(function(){
		var target = e.target

		jQuery.get("index_lite.php",
            {
                module:'newaccounts',
                action:'rpc_findProduct',
                findProduct:$("#new_item_name").val(), 
                priceType:document.getElementsByName('new[price_type]')[0].value,
                store_id: $("#store_id").val()
            },function(data){
			var d
			eval('d = '+data)
			if(!d)
				return

			var i,tbl,thead,tbody,th,tr,td,a,pane = document.getElementById('product_list_pane')
			pane.innerHTML = ""

			tbl = document.createElement('table')
			tbl.style.width = "100%"
			tbl.setAttribute('rules', 'cols')
			tbl.setAttribute('id', 'searchTable')
			thead = document.createElement('thead')
			tbl.appendChild(thead)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('ID'))
			thead.appendChild(th)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('ОтвОтдел'))
            th.setAttribute("title", "Ответственный отдел");
			thead.appendChild(th)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('Артикул'))
			thead.appendChild(th)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('**'))
            th.setAttribute("title", "Характеристика товара");
			thead.appendChild(th)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('Наименование'))
			thead.appendChild(th)

			th = document.createElement('th')
            var tn = document.createTextNode('Количество*');
            th.setAttribute("title", "доступно / склад / дальний склад");
			th.appendChild(tn);
			thead.appendChild(th)

			th = document.createElement('th')
            var tn = document.createTextNode('Склад');
			thead.appendChild(th)

			th = document.createElement('th')
			th.appendChild(document.createTextNode('Цена за единицу'))
			thead.appendChild(th)

			tbody = document.createElement('tbody')
			tbl.appendChild(tbody)

			for(i=0; i<d.length; i++){
				if(d[i] == null)
					continue

				tr = document.createElement('tr')
                tr.setAttribute("class", "searchTable_"+(i%2 == 0 ? "odd" : "even"));
				tr.style.cursor = 'pointer'
				tr.onmouseover = function(){
					var bc = this.style.backgroundColor
					this.onmouseout = function(){
						this.style.backgroundColor = bc
					}
					this.style.backgroundColor = '#ccffcc'
				}
				tbody.appendChild(tr)


				td = document.createElement('td')
				td.appendChild(document.createTextNode(d[i]['code']))
				tr.appendChild(td)

				td = document.createElement('td')
				td.appendChild(document.createTextNode(d[i]['division']))
				tr.appendChild(td)

				td = document.createElement('td')
				td.appendChild(document.createTextNode(d[i]['art']))
				tr.appendChild(td)

				td = document.createElement('td')
				td.appendChild(document.createTextNode(d[i]['description']))
				tr.appendChild(td)

				td = document.createElement('td')
				td.style.width = '90%'
				a = document.createElement('a')
				a.href = '#'
				a.appendChild(document.createTextNode(d[i]['name']))
				a.onclick = statlib.modules.newaccounts.mk1cBill.findProduct.fixProd(i,d)

                // товары с нулевой ценой можно теперь выбирать
				//if(parseInt(d[i]['price'])) // && (d[i]['is_service'] || parseInt(d[i]['quantity'])))
					td.appendChild(a)
				//else
				//	td.appendChild(document.createTextNode(d[i]['name']));

				tr.appendChild(td)


				if(parseInt(d[i]['price'])) // && (d[i]['is_service'] || parseInt(d[i]['quantity']) || parseInt(document.getElementsByName('is_rollback')[0].value)))
					td.onclick = statlib.modules.newaccounts.mk1cBill.findProduct.fixProd(i,d)

				td = document.createElement('td')
				td.style.textAlign = "left"
				if(d[i]['qty_free'] != "" || d[i]['qty_store'] != "" || d[i]['qty_wait'] != "" )
					td.innerHTML = "<b>"+d[i]['qty_free']+"</b>/"+d[i]['qty_store']+"/"+d[i]['qty_wait'];
				else
					td.appendChild(document.createTextNode('-'))
				tr.appendChild(td)
				if(parseInt(d[i]['price'])) // && (parseInt(document.getElementsByName('is_rollback')[0].value) || d[i]['is_service'] || parseInt(d[i]['quantity'])))
					td.onclick = statlib.modules.newaccounts.mk1cBill.findProduct.fixProd(i,d)

				td = document.createElement('td');
                if(d[i]['store'] == "yes"){
                    td.innerHTML = '<b style="color: green;">Склад</b>';
                }else
                    if(d[i]['store'] == "no"){
                        td.innerHTML = '<b style="color: blue;">Заказ</b>';
                    }else
                    if(d[i]['store'] == "remote"){
                        td.innerHTML = '<b style="color: #c40000;">ДалСклад</b>';
                    }
				tr.appendChild(td)

				td = document.createElement('td')
				td.style.textAlign = "left"
				td.appendChild(document.createTextNode(d[i]['price']+'р'))
				tr.appendChild(td)
				if(parseInt(d[i]['price'])) // && (parseInt(document.getElementsByName('is_rollback')[0].value) || d[i]['is_service'] || parseInt(d[i]['quantity'])))
					td.onclick = statlib.modules.newaccounts.mk1cBill.findProduct.fixProd(i,d)
			}

			pane.appendChild(tbl)
		})
	},200)
}
statlib.modules.newaccounts.mk1cBill.findProduct.fixProd = function(i,p){
	var idx = parseInt(i)
	var d = p
	return function(){
		document.getElementById('new_item_id').value = d[idx]['id']
		document.getElementById('new_item_name').value = d[idx]['name']
		document.getElementById('new_item_quantity').value = 1
		document.getElementById('new_item_append').checked = 'checked'
		document.getElementById('item_append_form').submit()
		return false
	}
}
statlib.modules.newaccounts.mk1cBill.findProduct.Timer = null

statlib.modules.tt.mktt.setState1c = function(ev,element){
	var state = element.value,
		form = document.getElementById('state_1c_form'),
		in_state = document.getElementById('state_1c_form_state')

	in_state.value = state

	form.submit()
}

statlib.modules.voip.show_price2 = function(ev, operator, defcode, date_before, diff_before, price_before, date_from, price, date_after, diff_after, price_after) {
	var e = ev || window.event
	if(!e.target) // IE fix
		e.target = e.srcElement
	
	var target_offset = $(e.target).offset();
	var popup = $('#popup_price');
	popup.css({left: target_offset.left, top: target_offset.top+$(e.target).height()+6})
	var text = '<b>'+operator+'</b><br/>Префикс: <b>'+defcode+'</b><br/>';
	if (date_from != '')
		text = text + '<b>Текущая цена: '+price+'</b> от '+date_from+'<br\>';
	if (date_before != '')
		text = text + 'Предыдущая цена: '+price_before+' от '+date_before+' ('+diff_before+'%)<br\>';
	if (date_after != '')
		text = text + 'Будущая цена: '+price_after+' от '+date_after+' ('+diff_after+'%)<br\>';
	popup.html(text);
	popup.show();
}

statlib.modules.voip.show_price = function(ev, defcode, effndef, date_from) {
	var e = ev || window.event
	if(!e.target) // IE fix
		e.target = e.srcElement
	
	var target_offset = $(e.target).offset();
	var popup = $('#popup_price');
	popup.css({left: target_offset.left, top: target_offset.top+$(e.target).height()+6})
	var text;
	if (defcode == effndef)
		text = effndef+' (префикс)<br/>';
	else
		text = '<b>'+effndef+' (префикс)</b><br/>';
	if (date_from != '')
		text = text + date_from+' (дата)<br\>';
	popup.html(text);
	popup.show();
}


statlib.modules.voip.hide_price = function(ev) {
	var popup = $('#popup_price');
	popup.hide();
	
}

