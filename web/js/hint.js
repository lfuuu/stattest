function hintReset(obj)
{
	if(obj.divRef)
	{
		if(obj.divRef.timerRef)
		{
			if( obj.divRef.timerRef != null)
			{
				clearTimeout(obj.divRef.timerRef);
				obj.divRef.timerRef = null;
			}
		}
	}
}


function changeDisplay( theElement, setTo ) {
    if( !theElement ) {
        return;
    }
    if( theElement.style ) { theElement = theElement.style; }
    if( typeof( theElement.display ) == 'undefined' ) {
        return;
    }
    theElement.display = setTo;
}

function hideHint(obj)
{
    var div = obj.divRef;

    hintReset(obj)

    div.timerRef = setTimeout("hideHintDiv('"+div.id+"')",500);

}

function hideHintDiv(div_id)
{
    var div = document.getElementById(div_id)
    changeDisplay( div , "none");
}

var divCounter = 0;

function showHint(obj, html, offset)
{
    pos = findPosition(obj);
    if(!offset)
    {
        offset = 0;
    }

    if(!obj.divId)
    {

        // get offset width text
        var myDiv1 = document.createElement("div");
            myDiv1.style.position = "absolute";
            myDiv1.style.width ="240px";
            myDiv1.style.left = "100px";
            myDiv1.style.top = "100px";
            myDiv1.innerHTML = "<span style=\"font: normal 8pt sans-serif;filter:alpha(opacity=0); opacity: 0;\">"+html+"<span>";
            document.body.appendChild(myDiv1);
        var offsetText  = myDiv1.clientHeight;
            document.body.removeChild(myDiv1);


        var myDiv = document.createElement("div");
            //myDiv.innerHTML = html;
            myDiv.innerHTML = getHintDiv(html);
			myDiv.style.zIndex = '20';
            /*
            myDiv.style.border='1px solid #c4c4c4';
            myDiv.style.background='#fff0f0';
            myDiv.style.padding="5px 5px 5px 5px";
            */
            myDiv.style.position = "absolute";
            //myDiv.style.font="normal 8pt Arial";
            myDiv.style.width ="250px";
            myDiv.style.left = pos[0]-30+"px";
            myDiv.style.top = pos[1]-20-offset-offsetText+"px";
            myDiv.id = "hint_div_"+divCounter;
            myDiv.timerRef = null;
            obj.divRef = myDiv;
            document.body.appendChild(myDiv);

        obj.divId = divCounter++;
    }else{
        var myDiv = obj.divRef;
    }
    changeDisplay(myDiv, "block");
}


function findPosition( oLink ) {
    if( oLink.offsetParent ) {
        for( var posX = 0, posY = 0; oLink.offsetParent; oLink = oLink.offsetParent ) {
            posX += oLink.offsetLeft;
            posY += oLink.offsetTop;
        }
        return [ posX, posY ];
    } else {
        return [ oLink.x, oLink.y ];
    }
}


var htmlMsgs = new Array();
    var aHints = document.body.getElementsByTagName("div");
    for(i=0; i<aHints.length;i++)
    {
        if(aHints[i].className)
        {
            if(aHints[i].className == "hint")
            {
                var htmlMsg = aHints[i].innerHTML;

                /*var myA = document.createElement("a");
                    myA.innerHTML = "<sup>?</sup>";
                    myA.className = 'hangup';
                    myA.href = "#";
                    myA.msg = htmlMsg;
                    
                    myA.onmouseover=function(){showHint(this, this.msg);}
                	myA.onmouseout=function(){hideHint(this)}
                    myA.onmousemove=function(){hintReset(this)}
                  */  
                var myS = document.createElement("span");
                    myS.innerHTML = "<sup>?</sup>";
                    myS.className = 'hangup';
                    myS.msg = htmlMsg;
                    
                    myS.onmouseover=function(){showHint(this, this.msg);}
                    myS.onmouseout=function(){hideHint(this)}
                    myS.onmousemove=function(){hintReset(this)}

                    aHints[i].parentNode.insertBefore(myS, aHints[i]);
            }
        }
    }

function getHintDiv(html)
{

    return '<!-div style="position: absolute; left: 100px; top: 100px;width: 200px;"--> <table border=0 width="100%" cellpadding=0 cellspacing=0> <tr> <td style="vertical-align: top; " align=left width="1%"> <div style="overflow: hidden; width: 4px; left: 0px; top: 0px; height: 4px; z-index: 1; position: relative;"> <img style=" border: 0px none ; margin: 0px; padding: 0px; position: absolute; left: 0px; top: 0px; width: 216px; height: 53px; -moz-user-select: none;" src="images/imap.png"/></div> </td> <td style="border-top: 1px solid #abadb3;width: 4px; background-color: #edffd0;"><img style="border: 0px none ; margin: 0px; padding: 0px; left: 0px; top: 0px; width: 1px; height: 1px; -moz-user-select: none;" src="images/imap.png"/></td> <td style="vertical-align: top; " align=right width="1%"> <div style="overflow: hidden; width: 4px; left: 0px; top: 0px; height: 4px; z-index: 1; position: relative; "> <img style=" border: 0px none ; margin: 0px; padding: 0px; position: absolute; left: -212px; top: 0px; width: 216px; height: 53px; -moz-user-select: none;" src="images/imap.png"/></div> </td> </tr> <tr> <td style="border-left: 1px solid #abadb3;width: 4px; background-color: #edffd0;"><img style="border: 0px none ; margin: 0px; padding: 0px; left: 0px; top: 0px; width: 1px; height: 1px; -moz-user-select: none;" src="images/imap.png"/></td> <td style="background-color: #edffd0; font: normal 8pt sans-serif;">'+html+'</td> <td style="border-right: 1px solid #abadb3;width: 4px; background-color: #edffd0;"><img style="border: 0px none ; margin: 0px; padding: 0px; left: 0px; top: 0px; width: 1px; height: 1px; -moz-user-select: none;" src="images/imap.png"/></td> </tr> <tr> <td style="vertical-align:bottom;" width="1%" align=left> <div style="overflow: hidden; width: 4px; left: 0px; top: 0px; height: 4px; z-index: 1; position: relative; "> <img style=" border: 0px none ; margin: 0px; padding: 0px; position: absolute; left: 0px; top: -44px; width: 216px; height: 53px; -moz-user-select: none;" src="images/imap.png"/></div> </td> <td style="border-bottom: 1px solid #abadb3;width: 4px; background-color: #edffd0;"><img style="border: 0px none ; margin: 0px; padding: 0px; left: 0px; top: 0px; width: 1px; height: 1px; -moz-user-select: none;" src="images/imap.png"/></td> <td style="vertical-align:bottom;" width="1%" align=right> <div style="overflow: hidden; width: 4px; left: 0px; top: 0px; height: 4px; z-index: 1; position: relative; "> <img style=" border: 0px none ; margin: 0px; padding: 0px; position: absolute; left: -212px; top: -44px; width: 216px; height: 53px; -moz-user-select: none;" src="images/imap.png"/></div> </td> </tr> <tr> <td> </td> <td> <div style="overflow: hidden; width: 21px; left: 20px; top: -1px; height: 6px; z-index: 1; position: relative; "> <img style=" border: 0px none ; margin: 0px; padding: 0px; position: absolute; left: -28px; top: -47px; width: 216px; height: 53px; -moz-user-select: none;" src="images/imap.png"/></div> </td> <td> </td> </tr> </table> <!--/div-->';
}
