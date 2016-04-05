if (typeof $.store == 'undefined') $.store = {};

$.store.orders =
{
  markOrderProcessed: function(order_id, action_message, token)
  {
    if (confirmDelete(action_message))
    {
      $.ajax(
      {
        type: 'post',
        data: 'action=mark_processed&order_id=' + order_id + '&token=' + token,
        dataType: 'json',
        url: $('form input[name="siteurl"]').val()
      }).success(function(data)
      {
        if (data.code == 'ok')
        {
          $('tr#row' + order_id)
           .find('td').wrapInner('<div style="display:block"/>').parent().find('td > div').slideUp(700, function()
           {
             $(this).parent().parent().remove();
             location.reload();
           });
        }
      }).error(function()
      {
        alert('Error changing order status');
      });
    }
    return false;
  }
};