if (typeof $.store == 'undefined') $.store = {};

$.store.taxes = 
{
  init: function() 
  {
    $('select[name="tax_country"]').change(function()
    {
      $('select[name="tax_state"]').html('');
      $('select[name="tax_state"]').removeClass('error-field');
      $('#state span').remove();

      var country = $('select[name="tax_country"]').val();

      if (country != '')
      {
        $.getJSON($('form input[name="siteurl"]').val(), { action: 'get_states', country: country }).success(function(data)
        {
          if (data.length > 1)
          {
            var options = '';
            for (var i = 0; i < data.length; i++) 
            {
              options += '<option value="' + data[i].v + '">' + data[i].t + '</option>';
            }
            $('select[name="tax_state"]').html(options);
            $('#state').show();
          }
          else
          {
            $('#state').hide();
          }
        }).error(function()
        { 
          $('#state').hide();
        });
      }
      else
      {
        $('#state').hide();
      }
    });
  }
};


$(document).ready(function()
{
  $.store.taxes.init();
});