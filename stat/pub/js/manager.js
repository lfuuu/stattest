
function prepareData(name)
{
	var serializedData = '';
	members = getE("members_"+name);
	for(i=0; i<members.length; i++)
	{
		serializedData += members.options[i].value+":";
	}
	getE("send_members_"+name).value = serializedData;
	return true;
}
function upMember(name)
{
	members = getE("members_"+name);
	sIndex = members.selectedIndex;
	if(sIndex != -1 && sIndex != 0)
	{
			var member = members.options[sIndex];
			var _member = members.options[sIndex-1];
			members.removeChild(member)
			members.insertBefore(member,_member)
			members.selectedIndex = sIndex-1;

	}
}

function downMember(name)
{

	members = getE("members_"+name);
	sIndex = members.selectedIndex;
	
	if(sIndex != -1)
	{
		if(sIndex != members.length-1)
		{
			var member = members.options[sIndex];
			var _member = members.options[sIndex+1];
			var _member3 = members.options[sIndex+2];
			members.removeChild(member)
			if(_member3)
			members.insertBefore(member,_member3)
			else
			members.appendChild(member)
			
		}
	}
}

function inMember(name)
{
	members = getE("members_"+name);
	no_members = getE("no_members_"+name);
	sIndex = no_members.selectedIndex;

	if(sIndex != -1)
	{
		tmpValue = no_members.options[sIndex].value;
		tmpText  = no_members.options[sIndex].text;

		no_members.remove(sIndex);
		no_members.selectedIndex = -1;

		var oOption = new Option(tmpText, tmpValue);
		members.options[members.length] = oOption;
	}
}

function outMember(name)
{

	members = getE("members_"+name);
	no_members = getE("no_members_"+name);
	sIndex = members.selectedIndex;

	if(sIndex != -1)
	{
		tmpValue = members.options[sIndex].value;
		tmpText  = members.options[sIndex].text;

		members.remove(sIndex);
		members.selectedIndex = -1;

		var oOption = new Option(tmpText, tmpValue);
		no_members.options[no_members.length] = oOption;
	}
}

function getE(elem)
{
    return document.getElementById(elem);
}

