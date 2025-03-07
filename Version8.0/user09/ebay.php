<?php
error_reporting(E_ALL);  // Turn on all errors, warnings and notices for easier debugging

// API request variables
$endpoint = 'http://svcs.ebay.com/services/search/FindingService/v1';  // URL to call
$version = '1.0.0';  // API version supported by your application
$appid = 'RobertMa-Shakopee-PRD-169ec6b8e-bb30ba02';  // Replace with your own AppID
$globalid = 'EBAY-US';  // Global ID of the eBay site you want to search (e.g., EBAY-DE)
$query = 'Star wars';  // You may want to supply your own query
$safequery = urlencode($query);  // Make the query URL-friendly
$i = '0';  // Initialize the item filter index to 0
// Create a PHP array of the item filters you want to use in your request
$filterarray =
    array(
        array(
            'name' => 'MaxPrice',
            'value' => '25',
            'paramName' => 'Currency',
            'paramValue' => 'USD'
        ),
        array(
            'name' => '',
            'value' => 'true',
            'paramName' => '',
            'paramValue' => ''
        ),
        array(
            'name' => 'ListingType',
            'value' => array('AuctionWithBIN', 'FixedPrice'),
            'paramName' => '',
            'paramValue' => ''
        ),
    );

// Generates an indexed URL snippet from the array of item filters
function buildURLArray($filterarray)
{
    global $urlfilter;
    global $i;
    // Iterate through each filter in the array
    foreach ($filterarray as $itemfilter) {
        // Iterate through each key in the filter
        foreach ($itemfilter as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $j => $content) { // Index the key for each value
                    $urlfilter .= "&itemFilter($i).$key($j)=$content";
                }
            } else {
                if ($value != "") {
                    $urlfilter .= "&itemFilter($i).$key=$value";
                }
            }
        }
        $i++;
    }
    return "$urlfilter";
} // End of buildURLArray function

// Build the indexed item filter URL snippet
buildURLArray($filterarray);


// Construct the findItemsByKeywords HTTP GET call
$apicall = "$endpoint?";
$apicall .= "OPERATION-NAME=findItemsByKeywords";
$apicall .= "&SERVICE-VERSION=$version";
$apicall .= "&SECURITY-APPNAME=$appid";
$apicall .= "&GLOBAL-ID=$globalid";
$apicall .= "&keywords=$safequery";
$apicall .= "&paginationInput.entriesPerPage=50";
$apicall .= "$urlfilter";
// Load the call and capture the document returned by eBay API
$resp = simplexml_load_file($apicall);

// Check to see if the request was successful, else print an error
if ($resp->ack == "Success") {
    $results = '';
    // If the response was loaded, parse it and build links
    foreach ($resp->searchResult->item as $item) {
        $pic   = $item->galleryURL;
        $link  = $item->viewItemURL;
        $title = $item->title;
        $price = $item->sellingStatus->currentPrice;

        /////////////////////////EDIT THIS LINE/////////////////////////////////////////////////////
        // For each SearchResultItem node, build a link and append it to $results
        if ($i % 2 == 0) {
            $results .= "<tr><td><img src=\"$pic\"></td><td><b>$price</b></td><td><a href=\"$link\">$title</a></td></tr>";
        } else {
            $results .= "<tr><td><hr></hr></div></td></tr>";
        }
        ////////////////////////EDIT THIS LINE//////////////////////////////////////////////////////      
        $i++;
    }
}
// If the response does not indicate 'Success,' print an error
else {
    $results  = "<h3>Oops! The request was not successful. Make sure you are using a valid ";
    $results .= "AppID for the Production environment.</h3>";
}
?>
<!-- Build the HTML page with values from the call response -->
<html>

<head>
    <title>eBay Search Results for <?php echo $query; ?></title>

    <!-- CSS -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Animate -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
    <!-- Custom -->
    <link rel="stylesheet" href="style.css">

    <!-- JavaScript -->
    <!-- These are needed to get the responsive menu to work -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style type="text/css">
        body {
            font-family: arial, sans-serif;
        }

        hr {
            margin-top: 2rem;
            margin-bottom: 2rem;
            border: 0;
            border-top: 2px solid rgba(0, 0, 0, 0.1);
            background-color: black;
        }
        td{
            border: 2px;
        }
    </style>
</head>

<body>

    <h1>eBay Search Results for <?php echo $query; ?></h1>

    <table>
        <tr>
            <td>
                <?php echo $results; ?>
            </td>
        </tr>
    </table>

</body>

</html>