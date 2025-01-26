<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Okshon - {{$auctionName}}</title>
    <style>
        h2:last-of-type() {
            padding-top: 2rem
        }
        p:last-of-type {
            padding-bottom: 10rem
        }
    </style>
</head>
<body>
    <h2><strong>Sender:</strong> </h2>
    <p><strong>Name:</strong> {{$senderName}}</p>
    <p><strong>Email:</strong> {{$senderEmail}}</p>
    <p><strong>Auction Location:</strong> {{$auctionLocation}}</p>
    <p><strong>Auction Name:</strong> {{$auctionName}}</p>
    <p><strong>Auction ID:</strong> {{$auctionId}}</p>
    
    <h2><strong>Recipient:</strong></h2>
    <p><strong>Name:</strong> {{$recipientName}}</p>
    <p><strong>Email:</strong> {{$recipientEmail}}</p>
    <p><strong>Address:</strong> {{$deliveryLocation}}</p>            
</body>
</html>