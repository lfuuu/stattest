var set = null;
	function addSchedule(){

		var new_div = $($('.schedule').get(0)).clone(true);
		$(new_div)
		.find(':select').each(function(){
			$(this).find('option:first').attr({'selected':'yes'});
		})
		.end()
		.find('.cross').html("<img src='images/del2.gif' border='0'>")
		.end()
		.find('.cross>img').click(removeSchedule)
		.end()
		.find(':input[name=name]').parents('tr').remove();
		$(new_div).appendTo('#work_graph');
	}
	
	function removeSchedule()
	{
		if(confirm('Удалить расписание?')){
			$($(this).parents('div.schedule').get(0)).remove();
		}
	}
	
	function get_time(div_elem)
	{
		var from = $(div_elem).find('tr.from>td.time');
		var to = $(div_elem).find('tr.to>td.time');
		var time_f = {'h':$(from).find(':select.hours option:selected').val(),'m':$(from).find(':select.minuts option:selected').val()}
		var time_t = {'h':$(to).find(':select.hours option:selected').val(),'m':$(to).find(':select.minuts option:selected').val()}
		
		var result = '*';
		if((time_f.h!='-')&&(time_f.m!='-')){
			if((time_t.h!='-')&&(time_t.m!='-')){
				result = {'from':time_f.h+':'+time_f.m,'to':time_t.h+':'+time_t.m}
			}
		}
		return result;	
	}//get_time
	
	function get_week_days_month(div_elem,tr_class)
	{
		var from	= null;
		var to		= null;
		var result	= '*';
	
		if(tr_class!=undefined){
			switch(tr_class){
				case 'week':
				case 'day':
				case 'month':
					from	= $(div_elem).find('tr.from>td.'+tr_class);
					to	= $(div_elem).find('tr.to>td.'+tr_class);
					
					var f = $(from).find(':select option:selected').val();
					var t = $(to).find(':select option:selected').val();
		
					if(f!='-'){
						if(t!='-'){
							result = {'from':f,'to':t}
						}
					}
					break;//CASE		
			}//SWITCH
		}//IF
		return result;
	}//get_week_days_month
	
	function save()
	{
		var scheduleArray = new Array();
		var sch_name = $(':input[name=name]').val();

		if((sch_name!='')&&(sch_name!=undefined)){
			$('#work_graph>div.schedule').each(function(){
				scheduleArray.push({
					'time'		:get_time(this),
					'weekday'	:get_week_days_month(this,'week'),
					'day'		:get_week_days_month(this,'day'),
					'month'		:get_week_days_month(this,'month')
				});
			});
			
			var xml_doc = "<?xml version='1.0'?>";
			xml_doc += "<schedule_graph>";
				xml_doc += "<name>"+sch_name+"</name>";
				for(var i=0;i<scheduleArray.length;i++){
					xml_doc += "<schedule>";
						for(var j in scheduleArray[i]){
							xml_doc += "<"+j+">";
								xml_doc += "<value>";	
								if(typeof(scheduleArray[i][j])=='object'){
									xml_doc += scheduleArray[i][j]['from']+'-'+scheduleArray[i][j]['to'];
								}else{
									xml_doc +=scheduleArray[i][j];
								}
								xml_doc += "</value>";
							xml_doc += "</"+j+">";
						}
					xml_doc += "</schedule>";
				}
			xml_doc += "</schedule_graph>";
			
			$.ajax({
				type:'post',
				url:'?module=ats&action=timecond&do=set&id='+set,
				data:{'data':xml_doc},
				success:function(msg){
					var res = eval('('+msg+')');
					if(res['result']!=undefined){
						var tmp = window.location.href;
						var reg = new RegExp('([a-zA-Z0-9/\.:]*)(?=[?])');
						var m	= reg.exec(tmp);
						if(m!=null){
							tmp = m[1]+res['result'];
							window.location.href = tmp;
						}
					}else{
						alert(res['error']);
					}
				}
			});
		}else{
			alert('Название расписания не может быть пустым!');
		}
	}
