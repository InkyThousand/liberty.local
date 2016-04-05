function isGroup(a) 
{
  return a ? !a.parentNode && a.length : false
}

function getGroupName(a) 
{
  return a[0] ? a[0].name : null
}

function getGroupValue(a) 
{
  if (a = document.getElementsByName(a)) if (a[0].type == "radio") for (var b = 0; b < a.length; b++) 
  {
    if (a[b].checked) return a[b].value
  } 
  else 
  {
    var c = [];
    for (b = 0; b < a.length; b++) a[b].checked && c.push(a[b].value);
    return c
  }
  return null
}

function find_id(a) 
{
  return document.getElementById(a)
}

function find_name(a)
{
  for (var b=0;b<document.forms.length;b++)
  if(document.forms[b][a])
    return document.forms[b][a];
}

function trimValues(a, b) 
{
  for (var c = 0; c < b.length; c++) 
  {
    var d = a.elements[b[c]];
    if (d && d.value) d.value = d.value.replace(/(\s*$)|(^\s*)/g, "")
  }
}

function lcValues(a,b)
{
  for(var c=0;c<b.length;c++)
  {
    var d=a.elements[b[c]];
    if(d&&d.value)d.value=d.value.toLowerCase()
  }
}

function getValue(a) 
{
  if (typeof a == "string") a = find_id(a) || find_name(a);
  if (a) if (isGroup(a)) return getGroupValue(getGroupName(a));
  else if (a.tagName == "INPUT") if (a.type.indexOf("checkbox") >= 0) return a.checked;
  else if (a.type.indexOf("file") >= 0) return a.value;
  else if (a.type.indexOf("hidden") >= 0) return a.value;
  else if (a.type.indexOf("password") >= 0) return a.value;
  else if (a.type.indexOf("radio") >= 0) 
  {
    if (a = document.getElementsByName(a.name)) 
    {
      for (var b = 0; b < a.length; b++) 
      {
        if (a[b].checked) return a[b].value;
      }
      return null
    }
  } 
  else 
  {
    if (a.type.indexOf("text") >= 0) return a.value
  } 
  else if (a.tagName == "TEXTAREA") return a.value;
  else if (a.tagName == "SELECT") if (a.multiple) 
  {
    var c = [];
    for (b = 0; a.options[b];) 
    {
      a.options[b].selected && c.push(a.options[b].value);
      b++
    }
    return c
  } 
  else
  return a.options[a.selectedIndex].value;
  return null
}

function getGroupValue(a) 
{
  if (a = document.getElementsByName(a)) if (a[0].type == "radio") for (var b = 0; b < a.length; b++) 
  {
    if (a[b].checked) return a[b].value
  } 
  else 
  {
    var c = [];
    for (b = 0; b < a.length; b++) a[b].checked && c.push(a[b].value);
    return c
  }
  return null;
}

function checkFieldNotEmpty(a, b, c)
{
  val = getValue(a);
  if (val == null || val == "")
  {
    c.push(a);
    showError(a, b);
  } 
  else
  {
    for (var d = b = 0; d < c.length; d++) 
    if (b = c[d] == a) break;
     b || eraseError(a)
  }
}

function checkFieldIsSame(a,b,c,d)
{
  var e=getValue(a);
  if(e==null)val1="";
  b=getValue(b);
  if(b==null)val2="";
  if(e==b)
  {
    for(e=c=0;e<d.length;e++)
     if(c=d[e]==a)
      break;
     c||eraseError(a)
  }
  else
  {
    d.push(a);
    return showError(a,c)
  }
}

function checkFieldRegexp(a, b, c, d) 
{
  var e = getValue(a);
  if (e == null) e = "";
  if (e.match(b)) 
  {
    for (c = b = 0; c < d.length; c++) if (b = d[c] == a) break;
    b || eraseError(a)
  } 
  else 
  {
    d.push(a);
    return showError(a, c)
  }
}

function showError(a, b) 
{
  if (typeof a == "string") a = find_id(a) || find_name(a);
  if (isGroup(a)) 
  {
     return showGroupError(a, b);
  }
  if (a) 
  {
    var c = a.id;
    var d = document.getElementById("error_" + c);
    if (d) 
    {
      d.innerHTML = b;
      d.style.display = "block"
    } 
    else 
    {
      d = document.createElement("div");
      d.setAttribute("id", "error_" + c);
      d.setAttribute("for", "error_" + c);
      d.className = "field_error";
      d.innerHTML = b;
      a.parentNode.appendChild(d)
    }
  }
  return false
}

function eraseError(a)
{
  if (typeof a=="string") a=find_id(a) || find_name(a);
  if (isGroup(a))
     return eraseGroupError(a);
  if(a)if(a=find_id("error_"+a.id))
  {
    a.innerHTML="";
    a.style.display="none"
  }
  return true
}

function eraseGroupError(a)
{
  if (a=find_id("error_g_"+a[0].name))
  {
    a.innerHTML="";
    a.style.display="none"
  }
  return true
}

function sendFocusTo(a) 
{
  if (typeof a == "string") a = find_id(a) || find_name(a);
  a.focus()
}

function sendFocusToGroup(a) 
{
  (a = document.getElementsByName(a)) && sendFocusTo(a[0])
}

function sendFocusToError(a) 
{
  a = a.getElementsByTagName("div");

  if (a.length) 
  {
    for (var b, c = 0; c < a.length; c++) if (a[c].className == "field_error" && a[c].innerHTML && a[c].id.indexOf("error_") == 0) 
    {
      b = a[c].id.substring(6);
      break
    }
    if (b) b.indexOf("g_") == 0 ? sendFocusToGroup(b.substring(2)) : sendFocusTo(b);
    return false
  }
}
