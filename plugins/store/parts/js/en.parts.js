if (typeof $.store == 'undefined') $.store = {};

$.store.parts =
{
  init: function()
  {
    $('#part_languages').change(function() { $.store.parts.showDescription(this) });
  },

  addPrice: function(sender)
  {
    $.ajax({
             type: "POST",
             data: 'action=add_price',
             url: $('form input[name="url"]').val()
           })
           .done(function(data)
           { 
             if ($("#prices tr").length == 2)
             {
               $(sender).closest("tbody").prepend(data);
             }
             else
             {
               $(sender).closest("tr").prev().after(data);
             }
           })
           .fail(function() 
           { 
             alert("Error retreiving data.");
           });
    return false;
  },

  deletePrice: function(sender)
  {
    $(sender).closest('tr').remove();
    return false;
  },

  showDescription: function(sender)
  {
    $("#part_description").val($('input[name=' + 'lang_' + sender.options[sender.selectedIndex].value + ']').val());
  }
};

$(document).ready(function()
{
  $.store.parts.init();
});