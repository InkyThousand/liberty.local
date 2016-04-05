use strict;

####################################
# Settings
my $smtpServer = 'moon.sysonline.com';
my $smtpTo     = 'libertyparts@libertypumps.com';
my $smtpSubj   = 'Request to access EzParts Catalog';

####################################
# HTMLs...
#
my $htmlForm = <<HTMLEND;
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<title>Request access form</title>
</head>
<body style="font-family:Verdana, Sans-Serif">
<table width="100%" height="100%">
<tr style="vertical-align:middle">
 <td>
  <form name="AccessRequest" method="POST">
   <table border="0" style="margin-left:auto;margin-right:auto;text-align:center">
    <tr>
      <td colspan="2"><img src="img/logo.gif" width="431" height="73" alt="LibertyPumps® Innovate. Evolve."></td>
    </tr>
    <tr>
      <td colspan="2"><p><b>Please fill this form to request access to parts catalog</b></p></td>
    </tr>
    <tr>
      <td colspan="2"><p style="color:red;font-size:11px"><b>All fields are required</b></p></td>
    </tr>
    <tr>
      <td align="right">Name:</td><td><input name="f_name" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Title:</td><td><input name="f_title" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Company Name:</td><td><input name="f_company" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Address:</td><td><input name="f_address" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">City:</td><td><input name="f_city" maxlength="200" size="60"></td>
    </tr>                                                
    <tr>
      <td align="right">State/Province:</td><td><input name="f_state" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Phone Number:</td><td><input name="f_phone" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Email Address:</td><td><input name="f_login" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td align="right">Requested Password:</td><td><input name="f_passwd" maxlength="200" size="60"></td>
    </tr>
    <tr>
      <td colspan="2" align="center"><input type="submit" name="Submit" value="Submit"></td>
    </tr>
  </table>
  </form>
 </td>
 </tr>
</table>
<script type="text/javascript">
document.forms[0].onsubmit = function()
{
  if (document.getElementsByName('f_name')[0].value == '')
  {
    alert('Please fill-in Name field.');
    document.getElementsByName('f_name')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_title')[0].value == '')
  {
    alert('Please fill-in Title field.');
    document.getElementsByName('f_title')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_company')[0].value == '')
  {
    alert('Please fill-in Company Name field.');
    document.getElementsByName('f_company')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_address')[0].value == '')
  {
    alert('Please fill-in Address field.');
    document.getElementsByName('f_address')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_city')[0].value == '')
  {
    alert('Please fill-in City field.');
    document.getElementsByName('f_city')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_state')[0].value == '')
  {
    alert('Please fill-in State/Province field.');
    document.getElementsByName('f_state')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_phone')[0].value == '')
  {
    alert('Please fill-in Phone Number field.');
    document.getElementsByName('f_phone')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_login')[0].value == '')
  {
    alert('Please fill-in Email Address field.');
    document.getElementsByName('f_login')[0].focus();
    return false;
  }
  if (document.getElementsByName('f_passwd')[0].value == '')
  {
    alert('Please fill-in Requested Password field.');
    document.getElementsByName('f_passwd')[0].focus();
    return false;
  }
  return true;
}
</script>
</body>
</html>
HTMLEND

my $htmlThankYou = <<HTMLEND;
<html>
<head>
</head>
<body style="font-family:Verdana, Sans-Serif;">
<table width="100%" height="100%">
<tr style="vertical-align:middle">
 <td>
   <table border="0" style="margin-left:auto;margin-right:auto;text-align:center">
    <tr>
      <td><img src="img/logo.gif" width="431" height="73" alt="LibertyPumps® Innovate. Evolve."></td>
    </tr>
    <tr>
      <td><p><b><br>Thank you for your request.<br>New account request for initial log-in and password will be processed within 24 hours.</b></p></td>
    </tr>
  <table>
 </td>
</tr>
</table>
</body>
</html>
HTMLEND

my $htmlError = <<HTMLEND;
<html>
<head>
</head>
<body style="font-family:Verdana, Sans-Serif;">
<table width="100%" height="100%">
<tr style="vertical-align:middle">
 <td>
   <table border="0" style="margin-left:auto;margin-right:auto;text-align:center">
    <tr>
      <td><img src="img/logo.gif" width="431" height="73" alt="LibertyPumps® Innovate. Evolve."></td>
    </tr>
    <tr>
      <td><p><b><br>There was some error during submitting your request.<br>
                Please contact us or try again later.</b></p></td>
    </tr>
  <table>
 </td>
</tr>
</table>
</body>
</html>
HTMLEND

###################################################
# Main part - do not edit!
#
#&ReadPostForm;
my %FORM = ();
my $FormData = '';
read(STDIN, $FormData, $ENV{'CONTENT_LENGTH'});
my @pairs = split(/&/, $FormData);
foreach my $pair (@pairs)
{
  my ($name, $value) = split(/=/, $pair);
  $value =~ tr/+/ /;
  $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
  $name = lc($name);
  $FORM{$name} = $value;
}
print "Content-type:text/html\n\n";

if (not exists($FORM{'f_passwd'}))
{
  print $htmlForm;
}
elsif(&SendEmail)
{
  print $htmlThankYou;
}
else
{
  print $htmlError;
}

exit 0;

################################################
# Sub-Routines
#
sub SendEmail
{
  use Net::SMTP;
  my $message = qq~<table border="0">
    <tr>
      <td align="right">Name:</td><td>$FORM{'f_name'}</td>
    </tr>
    <tr>
      <td align="right">Title:</td><td>$FORM{'f_title'}</td>
    </tr>
    <tr>
      <td align="right">Company Name:</td><td>$FORM{'f_company'}</td>
    </tr>
    <tr>
      <td align="right">Address:</td><td>$FORM{'f_address'}</td>
    </tr>
    <tr>
      <td align="right">City:</td><td>$FORM{'f_city'}</td>
    </tr>
    <tr>
      <td align="right">State/Province:</td><td>$FORM{'f_state'}</td>
    </tr>
    <tr>
      <td align="right">Phone Number:</td><td>$FORM{'f_phone'}</td>
    </tr>
    <tr>
      <td align="right">Requested Username:</td><td>$FORM{'f_login'}</td>
    </tr>
    <tr>
      <td align="right">Requested Password:</td><td>$FORM{'f_passwd'}</td>
    </tr>
  <table>~;

  my $smtp = Net::SMTP->new($smtpServer, Hello => 'parts.libertypumps.com', Timeout => 10);

  $smtp->mail('noreply@sysonline.com');
  $smtp->to($smtpTo);
  $smtp->data();
  $smtp->datasend("Date: " . localtime . "\n");
  $smtp->datasend("From: noreply\@sysonline.com\n");
  $smtp->datasend("To: $smtpTo\n");
  $smtp->datasend("Subject: $smtpSubj\n");
  $smtp->datasend("Content-Type: text/html; charset=windows-1251\n");
  $smtp->datasend("\n");
  $smtp->datasend($message);
  $smtp->dataend();
  $smtp->quit;

  return 1;

}
