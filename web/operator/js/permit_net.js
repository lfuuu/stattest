
function prepareIpNet(name)
{
    var o = document.getElementById("permit_net_"+name);
    var s = "";
    for(var i=0 ; i< o.length; i++)
    {
        s += (s=="" ? "" : ":")+o.options[i].text;
    }
    var m = document.getElementById("permit_net_save_"+name);
    m.value = s;
    return true;
}

function doDellIpNet(name)
{
    var o = document.getElementById("permit_net_"+name);
    if(o.selectedIndex != -1)
    {
        o.remove(o.selectedIndex);
    }
}
function doAddIpNet(name)
{
    ip = true;

    eval("var isNumber = typeof(is_"+name+"_number) != 'undefined';");

    if(!isNumber)
        ip = checkIp(name);

    if(ip)
    {
        net = checkMask(name);
        if(net)
        {
            mElem = document.getElementById("permit_net_"+name);
            new_value = ip+"/"+net;
            mElem.options[mElem.options.length] = new Option(new_value, new_value);

            document.getElementById("permit_add_net_"+name).value = "32";
            document.getElementById("permit_add_addr_"+name).value = "";
            document.getElementById("permit_add_addr_"+name).focus();
        }
    }
}
    function checkMask(name)
{
    netElem = document.getElementById("permit_add_net_"+name);
    net = netElem.value;
    if(net != '')
    {
        wNet = net.replace(new RegExp("[^0-9]+", 'gi'), '');
        if(wNet != net) netElem.value = wNet;
        if(wNet > 0 && wNet <= 32)
        {
            return wNet;
        }
    }
    eval("var isNumber = typeof(is_"+name+"_number) != 'undefined';");
    if(!isNumber)
    {
        alert("Маска сети задана не верно!");
    }else{
        alert("Количество линий задано не верно!");
    }
    netElem.focus();
    return false;
}
    function checkIp(name)
{
    ipElem = document.getElementById("permit_add_addr_"+name);
    ip = ipElem.value;
    if(ip != '')
    {
        wIp = ip.replace(new RegExp("[^0-9.]+", 'gi'), '');
        if(wIp != ip) ipElem.value = wIp;
        wIp = wIp.split(".");
        if(wIp.length == 4)
        {
            if(
                    wIp[0] >=0 && wIp[0] <= 255 && wIp[0] != "" &&
                    wIp[1] >=0 && wIp[1] <= 255 && wIp[1] != "" &&
                    wIp[2] >=0 && wIp[2] <= 255 && wIp[2] != "" &&
                    wIp[3] >=0 && wIp[3] <= 255 && wIp[3] != "" 
              )
            {
                ipElem.value = wIp.join('.');
                return ipElem.value;
            }
        }
        alert("Невправильный IP адрес! ");
        ipElem.focus();
        return false;
    }

    alert("IP адрес не задан!");
    ipElem.focus();
    return false;
}
