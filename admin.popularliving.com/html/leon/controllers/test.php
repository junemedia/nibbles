<?php
$string = <<<XML
<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><soap:Header><ResponseHeader xmlns="https://ws.campaigner.com/2013/01"><ErrorFlag>false</ErrorFlag><ReturnCode>M_1.1.1_SUCCESS</ReturnCode><ReturnMessage>Success.</ReturnMessage></ResponseHeader></soap:Header><soap:Body><DownloadReportResponse xmlns="https://ws.campaigner.com/2013/01"><DownloadReportResult><ReportResult AccountId="439960" Contactid="1886512849" ContactUniqueIdentifier="wgrant@gmail.com" FirstName="" LastName="" Email="wgrant@gmail.com" Phone="" Fax="" Status="subscribed" creationMethod="webserviceUpload" EmailFormat="both" DateCreatedUTC="2014-03-31T17:43:14.1970000" DateModifiedUTC="2014-04-11T17:41:11.1370000" hbOnUpload="False" IsTestContact="False" /><ReportResult AccountId="439960" Contactid="1886512879" ContactUniqueIdentifier="andis@junemedia.com" FirstName="" LastName="" Email="andis@junemedia.com" Phone="" Fax="" Status="subscribed" creationMethod="webserviceUpload" EmailFormat="both" DateCreatedUTC="2014-03-31T17:43:16.5570000" DateModifiedUTC="2014-04-08T11:35:23.8270000" hbOnUpload="False" IsTestContact="False" /><ReportResult AccountId="439960" Contactid="1894690819" ContactUniqueIdentifier="gharleycat@cs.com" FirstName="" LastName="" Email="gharleycat@cs.com" Phone="" Fax="" Status="subscribed" creationMethod="webserviceUpload" EmailFormat="both" DateCreatedUTC="2014-04-07T22:08:30.5400000" DateModifiedUTC="2014-04-08T16:11:17.7570000" hbOnUpload="False" IsTestContact="False" /></DownloadReportResult></DownloadReportResponse></soap:Body></soap:Envelope>
XML;

$xml = new SimpleXMLElement($string);
$ns = $xml->getNamespaces(true);
$xml->registerXPathNamespace('c', 'https://ws.campaigner.com/2013/01');

$result = $xml->xpath('//c:DownloadReportResult');

foreach ($result as $row) {
  var_dump($row);
}



$soap = $xml->children($ns['soap'])->body->children("https://ws.campaigner.com/2013/01");
var_dump($ns);
var_dump($soap);
var_dump($xml);
?>