var report_limit = {}
report_limit.findProduct = function(ev){
        var e = ev || window.event
        if(!e.target) // IE fix
                e.target = e.srcElement

        clearTimeout(report_limit.findProduct.Timer)
        report_limit.findProduct.Timer = setTimeout(function(){
                var target = e.target

                jQuery.get("index_lite.php",
            {
                module:'newaccounts',
                action:'rpc_findProduct',
                findProduct:$("#new_item_name").val(), 
                priceType:'NO',
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
                                a.appendChild(document.createTextNode(d[i]['name']))
                                a.onclick = report_limit.findProduct.fixProd(i,d)

                                td.appendChild(a)

                                tr.appendChild(td)


                                td = document.createElement('td')
                                td.style.textAlign = "left"
                                if(d[i]['qty_free'] != "" || d[i]['qty_store'] != "" || d[i]['qty_wait'] != "" )
                                        td.innerHTML = "<b>"+d[i]['qty_free']+"</b>/"+d[i]['qty_store']+"/"+d[i]['qty_wait'];
                                else
                                        td.appendChild(document.createTextNode('-'))
                                tr.appendChild(td)
                                td.onclick = report_limit.findProduct.fixProd(i,d)

                                td = document.createElement('td');
                                if(d[i]['store'] == "yes"){
                                    td.innerHTML = '<b style="color: green;">Склад</b>';
                                }else {
                                    if(d[i]['store'] == "no"){
                                        td.innerHTML = '<b style="color: blue;">Заказ</b>';
                                    }else
                                    if(d[i]['store'] == "remote"){
                                        td.innerHTML = '<b style="color: #c40000;">ДалСклад</b>';
                                    }
                                }
                                tr.appendChild(td)

                                td = document.createElement('td')
                                td.style.textAlign = "left"
                                td.appendChild(document.createTextNode(d[i]['price']+'р'))
                                tr.appendChild(td)
                                td.onclick = report_limit.findProduct.fixProd(i,d)
                        }

                        pane.appendChild(tbl)
                })
        },200)
}
report_limit.findProduct.fixProd = function(i,p){
        var idx = parseInt(i)
        var d = p
        return function(){
            var id = 'product_' + d[idx]['good_id'] + '_' + d[idx]['store_id'];
            var tr = document.getElementById(id);
            if (tr) return false;
            
            table = document.getElementById('products');
            tr = document.createElement('tr');
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            td.appendChild(document.createTextNode(d[idx]['code']));
            tr.appendChild(td);
                        
            td = document.createElement('td');
            td.style.textAlign = "left";
            td.appendChild(document.createTextNode(d[idx]['art']));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            td.appendChild(document.createTextNode(d[idx]['name']));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            td.appendChild(document.createTextNode(d[idx]['store_name']));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            td.appendChild(document.createTextNode((d[idx]['qty_free']) ? d[idx]['qty_free'] : '0'));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'text';
            input.name = 'products['+d[idx]['good_id']+']['+d[idx]['store_id']+']';
            input.value = (d[idx]['qty_free']) ? d[idx]['qty_free'] * 2 : 10;
            input.size = 5;
            td.appendChild(input);
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.style.textAlign = "left";
            del_id = '"#'+id+'"';
            img = document.getElementById('tmp_image');
            img_path = img.src;
            td.innerHTML ="<a onclick='$("+del_id+").remove();return false;'><img class='icon' alt='удалить' src='"+img_path+"'></a>";
            tr.appendChild(td);
            
            tr.id = id;
            table.appendChild(tr);

            return false
        }
}

report_limit.findProduct.Timer = null