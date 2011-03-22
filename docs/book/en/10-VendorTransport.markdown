Vendor Transports
=================

Vendor Specific transports are provided as a mean to more easily send emails
to your recipients. Those vendors are by no-way better than any others we just
for now don't have a transport implementation for other vendor. However, feel
free to submit any vendor we missed.

> The vendors are listed in alphabetical order, not by any kind of quality of 
> service or any subjective data

Amazon Simple Email Service
---------------------------

Amazon SES is provided by Amazon and allows you to send emails over the cloud
and using their own infrastructure. For more information about the service, 
please take a look http://aws.amazon.com/ses/ . To be able to use the 
Amazon SES Transport, you'll need to install and load the Amazon SDK, this
allows you to stay up-to-date and use other service without conflicts.
To download the SDK : http://aws.amazon.com/sdkforphp/

    [php]
    require_once AMAZON_PATH.'/sdk.class.php';
    require_once 'lib/swift_required.php';

	$localUrl='XXXX';
	
    $transport = 
    // Create the Transport to send simple mails over default server
    Swift_Swift_Transport_Vendors_AmazonSES::newInstance();
    
    // Alternatively Connect to a local Amazon Server
    // Swift_Swift_Transport_Vendors_AmazonSES::newInstance($localUrl);
    
    // Alternatively Connect to a local Amazon Server and send RAW mails
    // Swift_Swift_Transport_Vendors_AmazonSES::newInstance($localUrl, true);
    
    // Alternatively send RAW mails on default server
    // Swift_Swift_Transport_Vendors_AmazonSES::newInstance(null, true);
    
    $message = Swift_Message::newInstance();
    
    // Create your message content as usual 
    
    $mailer = Swift_Mailer::newInstance( $transport );
    $mailer->send($message);


