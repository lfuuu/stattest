var statlib = {}
statlib.modules = {}
statlib.modules.clients = {}
statlib.modules.newaccounts = {}
statlib.modules.tt = {}
statlib.modules.voip = {}

statlib.modules.clients.create = {}
statlib.modules.newaccounts.mk1cBill = {}
statlib.modules.newaccounts.bill_list_full = {}
statlib.modules.tt.mktt = {}

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

statlib.prepareAjaxSubmittingForm = function(formId, buttonId) {
	var form = $('#' + formId);
	var button = $('#' + buttonId);

	button.on('click', function(){
		button.attr('disabled', 'disabled');
		$.ajax( {
			type: "POST",
			url: form.attr('action'),
			data: form.serialize()
		}).always(function(){
				button.removeAttr('disabled');
			}).done(function(responce){
				if (responce.url) {
					location.href = responce.url;
				} else {
					showErrorModal(responce);
				}
			}).fail(function(responce){
				showErrorModal('Ошибка при отправке формы');
			});
	});
}

statlib.modules.newaccounts.bill_list_full.simple_tooltip = function (target_items, name){
	$(target_items).each(function(i){
		var id = $(this).attr('id');
		var timeout = null;
		$("body").append('<div class="'+name+'" id="tt_'+id+'"><p><img src="images/icons/delete.gif" alt="Удалить" ></p></div>');
		var my_tooltip = $("#tt_"+id);

		$(this).removeAttr("title").mouseover(function(){
			my_tooltip
				.css({opacity:0.8, display:"none", left:$(this).position().left+36, top:$(this).position().top-4});
			clearTimeout(timeout);
			timeout = setTimeout( '$("#tt_'+id+'").fadeIn(400);',1000 );
		}).mouseout(function(){
			clearTimeout(timeout);
			timeout = setTimeout( '$("#tt_'+id+'").fadeOut(400);',4000 );
		});

		my_tooltip.click(function(){
			if (confirm("Вы уверены, что хотите удалить документ?")) {
				$.ajax({
					type:"GET",
					url:"./",
					dataType:'html',
					data:{
						module:'newaccounts',
						action:'doc_file_delete',
						id:$(this).attr('id').replace(/tt_/g,"")
					},
					success:function(data){
						if (data == 'ok') {
							$("#"+id+".del_doc").fadeOut(500);
						} else {
							alert(data);
						}
						return;
					}
				});
			}
		}).mouseout(function(){
			clearTimeout(timeout);
			$(this).fadeOut(800);
		});
	});
}