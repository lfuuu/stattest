
function prepareMTN()
{
    var o = document.getElementById("mtn");
    var s = "";
    for(var i=0 ; i< o.length; i++)
    {
        s += (s=="" ? "" : ":")+o.options[i].value;
    }
    var m = document.getElementById("mtn_save");
    m.value = s;
    return true;
}

function doEditMTN()
{
    var o = document.getElementById("mtn");

    if(o.selectedIndex != -1)
    {
        optValue = o.options[o.selectedIndex].value;
        optText = o.options[o.selectedIndex].text;

        var r = optText.split(" - ");
        num = r[0];
        directionId = r[1];

        setViewSection("edit", o.selectedIndex, num, directionId);
    }
}

var mtn_selectedIndex = -1;
function setViewSection(action, selectedIndex, num, directionId)
{
    if(action == "edit")
    {
        mtn_selectedIndex = selectedIndex;
        getE("mtn_edit_number").value = num;
        setSelectValue(getE("mtn_edit_direction"), directionId);
        show("mtn_edit");
        hidd("mtn_add");
        hidd("mtn_keys");
    }else{
        hidd("mtn_edit");
        if(getE("mtn_numbers").options.length)
        {
            show("mtn_add");
        }else{
            hidd("mtn_add");
        }
        show("mtn_keys");
    }
}

function setSelectValue(obj, value)
{
    for(var i = 0; i<obj.options.length; i++)
    {
        if(obj.options[i].value == value)
        {
            obj.selectedIndex = i;
            return;
        }
    }    
}

function doApplyMTN()
{
    var o = getE("mtn");

    opt = o.options[mtn_selectedIndex];

    var r = opt.text.split(" - ");
    number = r[0];
    directionId = getSelectValue(getE("mtn_edit_direction"));

    var r = opt.value.split("=");
    numId = r[0];

    o.options[mtn_selectedIndex] = new Option(number+' - '+directionId, numId+'='+directionId);

    setViewSection("add");
}

function doCancelMTN()
{
    setViewSection("add");
}


function doDellMTN()
{
    var o = document.getElementById("mtn");

    if(o.selectedIndex != -1)
    {
        optText = o.options[o.selectedIndex].text;
        optValue = o.options[o.selectedIndex].value;

        o.remove(o.selectedIndex);

        no_members = getE("mtn_numbers");

        var r = optText.split(" - ");
        optText = r[0];

        var r = optValue.split("=");
        optValue = r[0];

        no_members.options[no_members.options.length] = new Option(optText, optValue);
        setViewSection("add");
    }
}

function doAddMTN()
{
    oMTN = getE("mtn_numbers");
    mtnId = oMTN.options[oMTN.selectedIndex].value;
    mtnText = oMTN.options[oMTN.selectedIndex].text;

    direction = getSelectValue(getE("mtn_direction"));

    mElem = document.getElementById("mtn");
    optText = mtnText+' - '+direction;
    optId = mtnId+'='+direction;
    mElem.options[mElem.options.length] = new Option(optText, optId);

	members = getE("mtn");
	no_members = getE("mtn_numbers");
	sIndex = no_members.selectedIndex;

	if(sIndex != -1)
    {
        tmpValue = no_members.options[sIndex].value;
        tmpText  = no_members.options[sIndex].text;

        no_members.remove(sIndex);
        no_members.selectedIndex = 0;
        setViewSection("add");
    }
}


