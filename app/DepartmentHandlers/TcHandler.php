<?php
namespace App\DepartmentHandlers;

use App\Helpers\Parsers;

class TcHandler extends DepartmentHandler
{

    public $indexUrl = 'http://wwwapps.tc.gc.ca/Corp-Serv-Gen/2/PDC-DPC/contract/reports.aspx';
    public $baseUrl = 'http://wwwapps.tc.gc.ca/';
    public $ownerAcronym = 'tc';

    // From the index page, list all the "quarter" URLs
    public $indexToQuarterXpath = "//form[@id='pageForm']//ul/li/a/@href";



    public $quarterToContractXpath = "//form[@id='pageForm']//table//td//a/@href";

    
    public function quarterToContractUrlTransform($contractUrl)
    {
        return "http://wwwapps.tc.gc.ca/Corp-Serv-Gen/2/PDC-DPC/contract/" . $contractUrl;
    }
    

    
    public function indexToQuarterUrlTransform($url)
    {
        return "http://wwwapps.tc.gc.ca/Corp-Serv-Gen/2/PDC-DPC/contract/" . $url;
    }
    

    public $contractContentSubsetXpath = "//form[@id='pageForm']";

    public function fiscalYearFromQuarterPage($quarterHtml)
    {

        // //*[@id="pageForm"]/p[2]/strong

        return Parsers::xpathRegexComboSearch($quarterHtml, "//form[@id='pageForm']/p[2]/strong", '/([0-9]{4})/');
    }

    public function fiscalQuarterFromQuarterPage($quarterHtml)
    {

        return Parsers::xpathRegexComboSearch($quarterHtml, "//form[@id='pageForm']/p[2]/strong", '/([0-9])</');
    }

    public function parseHtml($html)
    {

        return Parsers::extractContractDataViaGenericXpathParser($html, "//table//th[@scope='row']", "//table//td", ' to ');
    }
}
