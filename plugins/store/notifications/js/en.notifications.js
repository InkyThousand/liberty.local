if (typeof $.store == 'undefined') $.store = {};

$.store.notifications =
{
  showCheckModal: function()
  {
    $('#recipient_address').val('');
    $('#response').html('');
    $('#smtp_debug').attr('checked', false);
    $('#checkSMTPSettings').modal('show').width(600);
    return false;
  },

  clearResults: function()
  {
    $('#response').html('');
    return false;
  },

  sendMessage: function()
  {
    var use_smtp = $('#use_smtp').is(':checked');
    var smtp_host = $('#smtp_host').val();
    var smtp_port = $('#smtp_port').val();
    var smtp_auth_username = $('#smtp_auth_username').val();
    var smtp_auth_password = $('#smtp_auth_password').val();

    var from_name = $('#from_name').val();
    var from_address = $('#from_address').val();
    var recipient_address = $('#recipient_address').val();
    var smtp_debug = $('#smtp_debug').is(':checked');

    if (recipient_address == '')
    {
      alert('Please enter recipient address.');
      $('#recipient_address').focus();
      return false;
    }

    $(document).ajaxStop($.unblockUI);

    $.blockUI({ message: '<h3><img src="/public/assets/img/busy.gif" width="32" height="32"/>&nbsp;Trying to send...</h3>' });

    $.ajax({
            type: 'post',
            data: { 
                    action: 'check_smtp',
                    from_name: from_name,
                    from_address: from_address, 
                    recipient_address: recipient_address, 
                    smtp_debug: smtp_debug, 
                    use_smtp: use_smtp, 
                    smtp_host: smtp_host, 
                    smtp_port: smtp_port, 
                    smtp_auth_username: smtp_auth_username, 
                    smtp_auth_password: smtp_auth_password
                  },
            url: $('form input[name="siteurl"]').val()
          }).success(function(response)
             {
               $('#response').html(response);
             })
            .fail(function()
             {
               $('#response').html('Error performing request.');
             });

    return false;
  }
};