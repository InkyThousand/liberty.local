if (typeof $.store == 'undefined') $.store = {};

$.store.countries = 
{
  init: function() 
  {
    $('a#checkAll').click(function()
    {
      $(this).closest('form').find(":checkbox").attr("checked", true);
    });

    $('a#uncheckAll').click(function(e)
    {
      $(this).closest('form').find(":checkbox").attr("checked", false);
    });
  }
};


$(document).ready(function()
{
  $.store.countries.init();
});