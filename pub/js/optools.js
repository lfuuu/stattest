var optools = {
	inArray:function(val,eq){
		if(!eq)
			eq = false
		for(i in this){
			if(eq){
				if(this[i] === val)
					return true
			}else{
				if(this[i] == val)
					return true
			}
		}
		return false
	},
	check_submit:function(){
		if ($('select[name="dbform[t_id_tarif]"]').val() == '0') {
			alert("Тариф не выбран!");
			return false;
		}
		if ($('#is_trunk').val() == '1') return true;

		return optools.voip.check_e164.isValid()
	},
	check_vpbx_submit:function(){
		if ($('select[name="dbform[t_id_tarif]"]').val() == '0') {
			alert("Тариф не выбран!");
			return false;
		}

		return true;
	},
	friendly:{
		voip:{
			change_type:function(el){
				var type = el.value || el, types=['public','archive','special'], sel
				for(var i=0;i<3;i++){
					sel = document.getElementById('t_id_tarif_'+types[i])
					if(type==types[i]){
						sel.style.display = 'block'
						sel.name='dbform[t_id_tarif]'
					}else{
						sel.name=''
						sel.style.display = 'none'
					}
				}
			}
		},
		dates:{
			mon_right_days:[31,28,31,30,31,30,31,31,30,31,30,31],
			leap_years:[1988,1992,1996,2000,2004,2008,2012,2016,2020,2024,2028,2032,2036,2040,2044,2048,2052,2056,2060],
			check_mon_right_days_count:function(YId,mId,dId){
				if(typeof YId == 'string')
					YId = document.getElementsByName(YId)[0]
				if(typeof mId == 'string')
					mId = document.getElementsByName(mId)[0]
				if(typeof dId == 'string')
					dId = document.getElementsByName(dId)[0]

				var Yc = YId.childNodes,
					mC = mId.childNodes,
					dC = dId.childNodes,
					YSel, mSel, i

				for(i in Yc){
					if(Yc[i].selected)
						YSel = Yc[i].value
				}
				for(i in mC){
					if(mC[i].selected)
						mSel = mC[i].value
				}

				var rd
				if(optools.friendly.dates.leap_years.inArray(YSel) && parseInt(mSel)==2) // високосный год
					rd = 29
				else
					rd = optools.friendly.dates.mon_right_days[mSel-1]

				if(dC.length == rd)
					return
				else if(dC.length > rd){
					for(i=dC.length-1; i>rd-1;i--){
						dId.removeChild(dC[i])
					}
				}else{
					var op
					for(i=dC.length;i<rd;i++){
						op = document.createElement('option')
						op.appendChild(document.createTextNode(i+1))
						op.value=i+1
						dId.appendChild(op)
					}
				}
			}
		}
	},
	voip:{
		check_e164:{
			timeout:false,
			inputElement:false,
			old_number:'',
            region: '',
			is_valid:true,

			checkIsset:function(e164,imgEl){
				var imgEl = document.getElementById(imgEl)
				if(e164=='0000'){
					imgEl.src="images/icons/add.gif"
					imgEl.style.visibility = 'visible'

				}else
					$.get(
						"check_e164.php",
						{e164:'isset:'+e164},
						function(data){
							if(data == 'is')
								imgEl.src="images/icons/delete.gif";
							else
								imgEl.src="images/icons/add.gif";
							imgEl.style.visibility = "visible"
						}
					)
			},

			coloring:function(noncheck,inputEl,flags){
				with(optools.voip.check_e164){
					if(inputEl)
						var inputElement = inputEl

					inputElement.style.color = 'black'

                    return checking()
				}
			},

			set_timeout_check:function(inputElement){
				optools.voip.check_e164.is_valid = false;
				var img = document.getElementById('e164_flag_image');
				var img_els = img.src.split("/");
				img.style.visibility = "hidden";
				if(optools.voip.check_e164.old_number == inputElement.value){
					optools.voip.check_e164.is_valid = true;
					if(optools.voip.check_e164.timeout)
						window.clearTimeout(optools.voip.check_e164.timeout);
					return true;
				}

				if(optools.voip.check_e164.timeout)
					window.clearTimeout(optools.voip.check_e164.timeout);
				optools.voip.check_e164.inputElement = inputElement;
				optools.voip.check_e164.region = $("#region option:selected").val();

				if(inputElement.value.length < 4){
					optools.voip.check_e164.is_valid = false;
					img = document.getElementById('e164_flag_image');
					img_els = img.src.split("/");
					img_els[img_els.length-1] = "disable.gif";
					img.src = img_els.join("/");
					img.style.visibility = "visible";
					return false;
				}
                
				optools.voip.check_e164.timeout = window.setTimeout(optools.voip.check_e164.coloring, 800);
				return true;
			},
			checking:function(){
				if(optools.voip.check_e164.inputElement.value=='0000'){
					var img = document.getElementById('e164_flag_image')
					var img_els = img.src.split("/")
					optools.voip.check_e164.is_valid = true
					img_els[img_els.length-1] = "enable.gif"
					img.src = img_els.join("/")
					img.style.visibility = 'visible'
					return
				}
				$.get(
					'check_e164.php',
					{
						e164:optools.voip.check_e164.inputElement.value,
						region:optools.voip.check_e164.region,
						actual_from:document.getElementById('actual_from').value,
						actual_to:document.getElementById('actual_to').value
					},
					optools.voip.check_e164.result_parser
				);
			},
			result_parser:function(data){
				var img = document.getElementById('e164_flag_image');
				var img_els = img.src.split("/");
				img.style.visibility = "visible";
				if(data == 'true'){
					optools.voip.check_e164.is_valid = true;
					img_els[img_els.length-1] = "enable.gif";
					img.src = img_els.join("/");
					document.getElementById('e164_flag_letter').style.visibility = "hidden"
				}else if(data == 'true_but'){
					optools.voip.check_e164.is_valid = true
					img_els[img_els.length-1] = "enable.gif"
					img.src = img_els.join("/")
					document.getElementById('e164_flag_letter').style.visibility = "visible"
				}else{
					optools.voip.check_e164.is_valid = false;
					img_els[img_els.length-1] = "disable.gif";
					img.src = img_els.join("/");
					document.getElementById('e164_flag_letter').style.visibility = "hidden"
				}
			},
			get_free_e164:function(el){
				document.getElementById('actual_from').onkeyup = function(){document.getElementById('E164').onkeyup();return true}
				document.getElementById('actual_to').onkeyup = function(){document.getElementById('E164').onkeyup();return true}
				$.get(
					'check_e164.php',
					{
						e164:'FREE:'+el.value,
						actual_from:document.getElementById('actual_from').value,
						actual_to:document.getElementById('actual_to').value
					},
					function(data){
						document.getElementById('E164').value = data
						document.getElementById('E164').onkeyup()
					}
				);
				return false
			},
			get_free_e164_trunk:function(){
				$.get(
					'check_e164.php',
					{
						e164:'TRUNK'
					},
					function(data){
						document.getElementById('E164').value = data
						document.getElementById('E164').onkeyup()
					}
				);
				return false
			},
			isValid:function(){

                var oActualFrom = document.getElementById("actual_from");
                var oE164     = document.getElementById("E164");

                if(oActualFrom && oE164 && oActualFrom.value != "2029-01-01" && oE164.value == "7495")
                {
                    alert("Нельзя изменить дату, не установив номер линии!");
                    return false;
                }
				if(optools.voip.check_e164.is_valid)
					return true;
				else{
					alert('Пожалуйста, укажите корректный номер.\n1. Номер должен присутствовать в базе\n2. Номер не должен быть закреплен за другим пользователем в настоящее время.\n3. Номер находится в выбранном регионе');
					return false;
				}
			}
		}
	},
	tt:{
		refix_buffer:{
			trash:null,
			chckbxs:[],
			cntr:0,
			chckbxs2click:[]
		},

		timetable_buffer:{
			curday:null,
			curmonth:null,
			curyear:null,
			months:['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек']
		},

		doers_buffer:{
			inparea:null,
			curr_doer_area:null
		},

		timetable_cal_generate:function(dateObj){
			with(optools.tt.timetable_buffer){
				if(!curday || !curmonth || !curyear){
					var vdate = new Date();
					curday = vdate.getDay();
					curmonth = vdate.getMonth();
					curyear = vdate.getFullYear();
				}
			}

			var cc = {
				month:dateObj.getMonth(),
				flag:true,
				week_flag:true,
				append_html:'<tr  style="background-color:lightgray" align="center"><td>Пн</td><td>Вт</td><td>Ср</td><td>Чт</td><td>Пт</td><td>Сб</td><td>Вс</td></tr>',
				now_day:null,
				href:'?'
			}

			var fhref = optools.getLocationHrefVals();
			for(i in fhref){
				if(i != '#anchor')
					cc.href += i+'='+fhref[i]+'&';
			}

			vdate = dateObj;

			if(vdate.getDay() == 0){
				cc.append_html += '<tr style="background-color:lightgray" align="center"><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>';
			}else{
				cc.append_html += '<tr style="background-color:lightgray" align="center">'
				for(i=1 ; i<vdate.getDay() ; i++){
					cc.append_html += '<td>&nbsp;</td>';
				}
			}

			for(i=0 ; i<42 ; i++){
				if(!cc.week_flag){
					cc.append_html += '<tr style="background-color:lightgray" align="center">';
					cc.week_flag = true;
				}

				if(vdate.getDate() == optools.tt.timetable_buffer.curday && vdate.getMonth() == optools.tt.timetable_buffer.curmonth && vdate.getFullYear() == optools.tt.timetable_buffer.curyear)
					cc.append_html += '<td style="background-color:lightblue"><b><a href="'+cc.href+'ttt_d='+vdate.getDate()+'&ttt_m='+(vdate.getMonth()+1)+'&ttt_y='+vdate.getFullYear()+'" style="text-decoration:none">';
				else
					cc.append_html += '<td><a href="'+cc.href+'ttt_d='+vdate.getDate()+'&ttt_m='+(vdate.getMonth()+1)+'&ttt_y='+vdate.getFullYear()+'" style="text-decoration:none">';

				cc.append_html += vdate.getDate();

				if(vdate.getDate() != optools.tt.timetable_buffer.curday || vdate.getMonth() != optools.tt.timetable_buffer.curmonth || vdate.getFullYear() != optools.tt.timetable_buffer.curyear)
					cc.append_html += '</a></td>';
				else
					cc.append_html += '</a></b></td>';

				cc.now_day = vdate.getDay();
				vdate.setTime(vdate.getTime() + (60*60*24*1000));

				if(cc.now_day == vdate.getDay())
					vdate.setTime(vdate.getTime() + (60*60*1000))

				if(cc.now_day == 0){
					cc.week_flag = false;
					cc.append_html += '</tr>';
				}

				if(vdate.getMonth() != cc.month){
					if(cc.now_day == 0)
						break;
					for(i=cc.now_day ; i<7 ; i++){
						cc.append_html += '<td>&nbsp;</td>';
					}
					cc.append_html += '</tr>';
					break;
				}
			}

			document.getElementById('timetable_cal_panel').innerHTML = cc.append_html;
		},

		timetable_event_handler:function(event){
			var cur={year:null,month:null},
				source = optools.getEventSource(event),
				action;

			action = source.id;

			cur.year = parseInt(document.getElementById('timetable_cal_year_area').value);
			cur.month = document.getElementById('timetable_cal_month_area').value;

			with(optools.tt.timetable_buffer){
				for (mo in months){
					if(months[mo] == cur.month)
						cur.month = mo;
				}
			}

			switch(action){
				case 'year_dec':{
					cur.year--;
					document.getElementById('timetable_cal_year_area').value = cur.year;
					break;
				}case 'year_inc':{
					cur.year++;
					document.getElementById('timetable_cal_year_area').value = cur.year;
					break;
				}case 'month_dec':{
					cur.month--;
					if(cur.month < 0){
						cur.month = 11;
						cur.year--;
						document.getElementById('timetable_cal_year_area').value = cur.year;
					}
					document.getElementById('timetable_cal_month_area').value = optools.tt.timetable_buffer.months[cur.month];
					break;
				}case 'month_inc':{
					cur.month++;
					if(cur.month > 11){
						cur.month = 0;
						cur.year++;
						document.getElementById('timetable_cal_year_area').value = cur.year;
					}
					document.getElementById('timetable_cal_month_area').value = optools.tt.timetable_buffer.months[cur.month];
					break;
				}case 'hide_cal':{
					document.getElementById('timetable_cal_panel_frame').style.visibility = 'hidden';
					break;
				}case 'show_cal':{
					var d = new Date();
					cur.month = d.getMonth();
					cur.year = d.getFullYear();

					document.getElementById('timetable_cal_month_area').value = optools.tt.timetable_buffer.months[cur.month];
					document.getElementById('timetable_cal_year_area').value = cur.year;

					optools.tt.timetable_buffer.curday = d.getDate();
					optools.tt.timetable_buffer.curmonth = d.getMonth();
					optools.tt.timetable_buffer.curyear = d.getFullYear();

					coords = optools.getFullOffset(source);

					document.getElementById('timetable_cal_panel_frame').style.left = coords.x - document.getElementById('timetable_cal_panel_frame').offsetWidth/2;
					document.getElementById('timetable_cal_panel_frame').style.top = coords.y;
					document.getElementById('timetable_cal_panel_frame').style.visibility = 'visible';
					break;
				}
			}

			optools.tt.timetable_cal_generate(new Date(cur.year,cur.month,1));
			return false;
		},

		doer_edit_pane_popdown:function(event){
			setTimeout(new Function("optools.tt.doers_buffer.curr_doer_area.style.visibility='hidden';"),100);
		},

		doer_edit_pane_popup:function(event){
			if(optools.tt.doers_buffer.curr_doer_area)
				optools.tt.doers_buffer.curr_doer_area.style.visibility='hidden';
			var source = optools.getEventSource(event);
			var doer_id = source.id.split('_')[2];
			var coords = optools.getFullOffset(source);
			var edit_pane = document.getElementById('doer_edit_pane_'+doer_id);
			edit_pane.style.left = coords.x - edit_pane.offsetWidth / 2;
			edit_pane.style.top = coords.y - edit_pane.offsetHeight / 2;
			edit_pane.style.visibility = 'visible';
			optools.tt.doers_buffer.curr_doer_area = edit_pane;
		},

		doers_departs_popdown:function(event){
			setTimeout(new Function("document.getElementById('deps_store').style.visibility='hidden'"),100);
		},

		doers_departs_popup:function(event){
			var inparea = optools.getEventSource(event);
			var deps_store = document.getElementById('deps_store');
			var coords = optools.getFullOffset(inparea);
			deps_store.style.top = coords.y+inparea.offsetHeight;
			deps_store.style.left = coords.x;
			deps_store.style.width = inparea.offsetWidth;
			deps_store.style.visibility = 'visible';
			optools.tt.doers_buffer.inparea = inparea;
		},

		registerAutoClick:function(chckbxName){
			with(optools.tt.refix_buffer){
				chckbxs2click[chckbxs2click.length] = chckbxName;
			}
		},

		tt_autoClick:function(){
			var isnum = /^[0-9]+$/;
			for(i in optools.tt.refix_buffer.chckbxs2click){
				if(!isnum.test(i))
					continue;
				document.getElementsByName(optools.tt.refix_buffer.chckbxs2click[i])[0].click();
			}
		},

		ctl_chckbxs:function(element){
			var action,
				regex = /^doer_fix\[(\d\d\d\d\-\d\d\-\d\d)\]\[([0-9]+)\]/i,
				isnum = /^[0-9]+$/,
				inps = null,
				buf = null,
				buff = null;
			buff = element.getAttribute('name').match(regex);

			if(!element.checked){
				action = 'visible';
				optools.tt.refix_buffer.cntr--;
				if(optools.tt.refix_buffer.cntr > 0)
					return false;
			}else{
				action = 'hidden';
				optools.tt.refix_buffer.cntr++;
			}

			with(optools.tt.refix_buffer){
				if(chckbxs.length==0){
					inps = document.getElementsByTagName('input');
					for(i in inps){
						if(isnum.test(i) == false || !inps[i] || !inps[i].getAttribute('name'))
							continue;
						if(regex.test(inps[i].getAttribute('name')))
							chckbxs[chckbxs.length] = inps[i];
					}
				}
				for(i in chckbxs){
					if(isnum.test(i) == false)
						continue;
					if(action == 'hidden'){
						buf = chckbxs[i].getAttribute('name').match(regex);
						if(buf[1] == buff[1] && buf[2] == buff[2]){
							continue;
						}
					}
					chckbxs[i].style.visibility = action;
				}
			}
		},

		refix_doers:function(obj){
			var locvars = optools.getLocationHrefVals(),
				els = document.getElementsByTagName('input'),
				buf = '',
				bufmatch,
				regex = /doer\[([0-9]+)\]/i,
				isnum = /^[0-9]+$/;

			for(var i in els){
				if(isnum.test(i) == false || !els[i] || !els[i].getAttribute('name'))
					continue;
				bufmatch = els[i].getAttribute('name').match(regex);
				if(bufmatch && els[i].checked)
					buf += bufmatch[1]+",";
			}

			$.ajax({
				async:false,
				cache:false,
				type:"POST",
				url:"index_lite.php",
				dataType:'json',
				data:{
					module:'tt',
					action:'refix_doers',
					trouble:locvars['id'],
					doers:buf
				},
				beforeSend:function(){
					obj.style.visibility = "hidden";
				},
				success:function(data){
					switch(data.err){
						case 'ok':{
							alert('Фиксирование успешно завершено.\nGood luck!');
							break;
						}default:{
							alert('Ошибка!\nПожалуйста попробуйте еще раз, либо обратитесь к программисту\nКод ошибки: '+data.err);
							break;
						}
					}
					obj.style.visibility = "visible";
					return;
				}
			});
			return false;
		}
	},
	pays:{
		check_all_pay_radio:function(event){
			if(!event)
				var event = window.event;
			var chbx = optools.getEventSource(event);
			var sflag = chbx.checked;
			var patt = /pay\[\d+\]\[client\]/;
			var els = document.getElementsByTagName('input');
			for(var i in els){
				if(typeof els[i] != 'object')
					continue;
				if(!els[i].getAttribute)
					continue;
				if(els[i].getAttribute('type') != 'radio')
					continue;
				if(!patt.test(els[i].getAttribute('name')))
					continue;
				if(els[i].getAttribute('disabled')===true || els[i].getAttribute('disabled') == 'disabled')
					continue;
				if(els[i].click)
					els[i].click()
				else
					els[i].onclick()
			}
			return;
		},
		sel_all_pay_radio:function(evt){
			if(!evt)
				var evt = window.event
			var chbx = optools.getEventSource(evt),
				sflag = chbx.checked,
				patt = /pay\[\d+\]\[client\]/,
				par = document.getElementById('pays_tbl'),i,j,r,e,els,el,tblCell
			for(i in par.childNodes){
				if(typeof par.childNodes[i]!='object' || !par.childNodes[i].nodeName || par.childNodes[i].nodeName.toLowerCase()!='tbody')
					continue
				els = par.childNodes[i]
				if(!els.childNodes)
					continue
				break
			}
			if(!els.childNodes)
				return false
			for(j in els.childNodes){
				if(typeof els.childNodes[j] != 'object')
					continue;
				for(r in els.childNodes[j].childNodes){
					tblCell = els.childNodes[j].childNodes[r]
					if(typeof tblCell != 'object' || tblCell.nodeName.toLowerCase()!='td')
						continue
					for(e in tblCell.childNodes){
						el = tblCell.childNodes[e]
						if(typeof el != 'object' || !el.nodeName || el.nodeName.toLowerCase()!='input')
							continue
						if(!el.getAttribute)
							continue
						if(el.getAttribute('type') != 'radio')
							continue
						if(!patt.test(el.getAttribute('name')))
							continue
						if(el.getAttribute('disabled')===true || el.getAttribute('disabled') == 'disabled')
							continue
						if(el.getAttribute('value')=='')
							continue
						//els[i].checked = sflag;
						el.click();
					}
				}
			}
		}
	},
	bills:{
		bill_no:null,
		item_sort:null,
		getItemDateTable:function(date_from,date_to){
			document.getElementById('billItemDateTable_date_from').value = date_from
			document.getElementById('billItemDateTable_date_to').value = date_to
			return document.getElementById('ItemsDatesTable')
		},
		changeBillItemDate:function(event,bill_no,sort_number){
			var db,src,offs

			$.ajax({
				type:'GET',
				url:'index_lite.php',
				data:"module=newaccounts&action=bill_data&subaction=getItemDates&bill_no="+bill_no+"&sort_number="+sort_number,
				cache:false,
				async:false,
				success:function(data){
					eval('db='+data)
				}
			})

			optools.bills.bill_no = bill_no
			optools.bills.item_sort = sort_number

			src = optools.getEventSource(event)

            off_ = $(src).offset();
            //offs = {x: off_.left, y: off_.top};

			//offs = optools.getFullOffset(src)
            //


			table = optools.bills.getItemDateTable(db.date_from,db.date_to)
            table = $(table);
            console.log(src);

            /*
			table.style.display = 'block'
			table.style.top = off_.top;//offs.y - table.offsetHeight / 2 + src.offsetHeight/2
			table.style.left = off_.left;//offs.x - table.offsetWidth / 2 + src.offsetWidth /2
            */
            table.css("top", off_.top+35).css("left", off_.left-50).css("display", "block");

            console.log(table);
		},
		fixItemDate:function(){
			var db,date_from,date_to

			$.ajax({
				type:'GET',
				url:'index_lite.php',
				data:"module=newaccounts&action=bill_data&subaction=getItemDates&bill_no="+optools.bills.bill_no+"&sort_number="+optools.bills.item_sort,
				cache:false,
				async:false,
				success:function(data){
					eval('db='+data)
				}
			})
			date_from = document.getElementById('billItemDateTable_date_from').value
			date_to = document.getElementById('billItemDateTable_date_to').value

			if(date_from == db.date_from && date_to == db.date_to){
				optools.bills.getItemDateTable().style.display = 'none'
			}else{
				$.ajax({
					type:'GET',
					url:'index_lite.php',
					data:"module=newaccounts&action=bill_data&subaction=setItemDates&bill_no="+optools.bills.bill_no+"&sort_number="+optools.bills.item_sort+"&from="+date_from+"&to="+date_to,
					cache:false,
					async:false,
					success:function(data){
						if(data == 'Ok'){
							alert('Дата успешно изменена!')
							location.reload()
							return
						}else if(data == 'MySQLErr'){
							alert('Произошла ошибка на стороне сервера!\nНе удалось изменить дату.')
							return
						}else if(data == 'InvalidFormat'){
							alert('Неправильный формат даты.\nВерный формат: гггг-мм-дд')
							return
						}
					}
				})
			}
		}
	},
	getFullOffset:function(element){
		var x=parseInt(element.offsetLeft);
		var y=parseInt(element.offsetTop);
		if(element.offsetParent != null){
			var xy = this.getFullOffset(element.offsetParent);
			x = x+xy.x;
			y = y+xy.y;
		}
		return {x:x,y:y};
	},
	getEventSource:function(e){
		var ret;
		if(e.target)
			ret = e.target;
		else
			ret = e.srcElement;
		if (ret.nodeType == 3) // defeat Safari bug
			ret = locator.parentNode;
		return ret;
	},
	getLocationHrefVals:function(){
		var ret = {},buf;
		var ops = document.location.href.split("?");
		if(ops[1] == '')
			return false;
		ops = ops[1].split("#");
		ret['#anchor'] = ops[1];
		ops = ops[0].split("&");
		for(var val in ops){
			buf = ops[val].split("=");
			ret[decodeURIComponent(buf[0])] = decodeURIComponent(buf[1]);
		}
		return ret;
	},
	cookieProc:function(operation,index,value){
		var pairs,i, buf
		if(operation != 'get' && operation != 'set')
			return null
		if(operation == 'set'){
			document.cookie = index + '=' + value
		}else if(operation == 'get'){
			pairs = document.cookie.split(';')
			for(i=0;i<pairs.length;i++){
				buf = optools.trim(pairs[i]).split('=')
				if(buf[0]==index)
					return buf[1]
			}
			return null
		}
	},
	trim:function(string,rol){
		var buf, i, first, last
		buf = string.split(' ')
		for(i=0;i<buf.length;i++){
			if(buf[i]!=''){
				if(!first){
					first = i
					last = i+1
				}else
					last = i
			}
		}
		last = buf.length - last
		if(rol == 'r')
			first = 0
		else if(rol == 'l')
			last = 0
		return string.substring(first,string.length-last)
	},
	DatePickerInit: function(prefix){
		prefix = prefix || '';
		$( '#' + prefix + 'date_from' ).datepicker({
			dateFormat: 'dd-mm-yy',
			maxDate: $( '#' + prefix + 'date_to' ).val(),
			onClose: function( selectedDate ) {
				$( '#' + prefix + 'date_to' ).datepicker( 'option', 'minDate', selectedDate );
			}
		});
		$( '#' + prefix + 'date_to' ).datepicker({
			dateFormat: 'dd-mm-yy',
			minDate: $( '#' + prefix + 'date_from' ).val(),
			onClose: function( selectedDate ) {
				$( '#' + prefix + 'date_from' ).datepicker( 'option', 'maxDate', selectedDate );
			}
		});
	}
}
optools.friendly.dates.mon_right_days.inArray = optools.friendly.dates.leap_years.inArray = optools.inArray

optools.friendly.checkAllInSelect = function(sel){
	var selEl,i
	if(typeof sel == 'string')
		selEl = document.getElementById(sel) || document.getElementsByName(sel)[0]
	else
		selEl = sel

	for(i=0;i<selEl.childNodes.length;i++)
		if(selEl.childNodes[i].nodeName == 'OPTION')
			selEl.childNodes[i].selected = !selEl.childNodes[i].selected

}

$(document).ready(function() {
	$('#region').change(function(){
		var region_id = $(this).val();
		if ($('#get_free_e164').length) {
			var get_free_e164 = $('#get_free_e164');
			get_free_e164.find('[value!="null"][ value!="short"]').remove();
			$('#E164').val('');
			$('#e164_flag_image').css('visibility', 'hidden');
	
			if (region_id == '99') {
				get_free_e164
					.append("<option value='7499685'>7(499) 685</option>")
					.append("<option value='7499213'>7(499) 213</option>")
					.append("<option value='7495'>7(495)</option>");
			} else if(region_id == '97') {
				get_free_e164
					.append("<option value='7861204'>7(861) 204</option>");
			} else if(region_id == '98') {
				get_free_e164
					.append("<option value='7812'>7(812)</option>");
			} else if(region_id == '95') {
				get_free_e164
					.append("<option value='7343302'>7(343) 302</option>");
			} else if(region_id == '96') {
				get_free_e164
					.append("<option value='7846215'>7(846) 215</option>");
			} else if(region_id == '94') {
				get_free_e164
					.append("<option value='7383312'>7(383) 312</option>");
			} else if(region_id == '93') {
				get_free_e164
					.append("<option value='7843207'>7(843) 207</option>");
			} else if(region_id == '89') {
				get_free_e164
					.append("<option value='7423206'>7(423) 206</option>");
			} else if(region_id == '87') {
				get_free_e164
					.append("<option value='7863309'>7(863) 309</option>");
			}
		}
		getTarifs(region_id);
	});
	
	$('#is_trunk').change(function(){
		if($(this).val() == '1') {
			$('#get_free_e164, #e164_flag_image, #e164_flag_letter').hide();
			optools.voip.check_e164.get_free_e164_trunk();
			$('#s_tarif_type').val('special').change();
			$('#tr_E164 td:first-child').html('номер транка');
		} else {
			$('#get_free_e164, #e164_flag_image, #e164_flag_letter').show();
			$('#s_tarif_type').val('public').change();
			$('#tr_E164 td:first-child').html('номер телефона');
		}
	});
});
function getTarifs(region_id)
{
	//dest == 4
	$('#t_id_tarif_public').empty();
	$('#t_id_tarif_archive').empty();
	$('#t_id_tarif_special').empty();
	//dest == 1
	$('#t_id_tarif_russia').empty();
	//dest == 3
	$('#t_id_tarif_sng').empty();
	//dest == 2
	$('#t_id_tarif_intern').empty();	
	//dest == 5
	$('#t_id_tarif_local_mob').empty();
	
	$.ajax({
		type:'GET',
		url:'index_lite.php',
		data:"module=services&action=get_tarifs&region="+region_id,
		dataType: "json",
		success: function(data){
			$.each(data, function( k, v ) {
				if (v.dest == 1) {
					$('#t_id_tarif_russia').append('<option value="'+v.id+'">'+v.name+' ('+v.month_min_payment+')</option>');
				} else if (v.dest == 2) {
					$('#t_id_tarif_intern').append('<option value="'+v.id+'">'+v.name+' ('+v.month_min_payment+')</option>');
				} else if (v.dest == 3) {
					$('#t_id_tarif_sng').append('<option value="'+v.id+'">'+v.name+' ('+v.month_min_payment+')</option>');
				} else if (v.dest == 4) {
					$('#t_id_tarif_' + v.status).append('<option value="'+v.id+'">'+v.name+' ('+v.month_number+'-'+v.month_line+')</option>');
				} else if (v.dest == 5) {
					$('#t_id_tarif_local_mob').append('<option value="'+v.id+'">'+v.name+' ('+v.month_min_payment+')</option>');
				}
			});
		}
	});

}
