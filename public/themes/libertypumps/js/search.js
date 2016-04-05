function trapEnterKey(e,functionToCall)
{
    var characterCode;
    if(e&&e.which)
    {//NS
        e=e;
        characterCode=e.which;
    }else{//IE
        e=event;
        characterCode=e.keyCode;
    }
    if(characterCode==13||characterCode==10||characterCode==11){//enter
        if(functionToCall!=null&&functionToCall!='')setTimeout(functionToCall,0);
        return false;
    }
}

function searchSubmit(){
    var f=document.forms[0];
    var kw=f.kw.value;
    if(kw.length>0&&kw!='Keyword/Product Name'){
        location='https://www.libertypumps.com/Search/Results.aspx?q='+escape(kw);
    }
}