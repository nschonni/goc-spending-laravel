<?php
namespace App\DepartmentHandlers;

use App\DepartmentHandler;
use App\Helpers;

class StatsHandler extends DepartmentHandler {

	public $indexUrl = 'https://www.statcan.gc.ca/eng/about/contract/report';
	public $baseUrl = 'https://www.statcan.gc.ca/';
	public $ownerAcronym = 'stats';

	// From the index page, list all the "quarter" URLs
	public $indexToQuarterXpath = "//div[@role='main']//ul/li/a/@href";

	public $multiPage = 0;

	public $quarterToContractXpath = "//div[@role='main']//table//td//a/@href";

	/*
	public function quarterToContractUrlTransform($contractUrl) {
		return "http://www.cic.gc.ca/disclosure-divulgation/" . $contractUrl;
	}

	public function indexToQuarterUrlTransform($url) {
		return "http://www.cic.gc.ca/disclosure-divulgation/" . $url;
	}*/


	// Ignore the latest quarter that uses "open.canada.ca" as a link instead.
	// We'll need to retrieve those from the actual dataset.
	public function filterQuarterUrls($quarterUrls) {

		// Remove the new entries with "open.canada.ca"
		$quarterUrls = array_filter($quarterUrls, function($url) {
			if(strpos($url, 'open.canada.ca') !== false) {
				return false;
			}
			return true;
		});

		return $quarterUrls;
	}


	public $contractContentSubsetXpath = "//div[@role='main']";

	public function fiscalYearAndQuarterFromTitle($quarterHtml, $output ='year') {

		$quarterIndex = [
			'Fourth quarter' => 4,
			'First quarter' => 1,
			'Second quarter' => 2,
			'Third quarter' => 3,
		];

		$fiscalYear = Helpers::xpathRegexComboSearch($quarterHtml, "//h1[@id='wb-cont']", '/([0-9]{4})/');
		$fiscalQuarter = '';


// \w
		$title = Helpers::xpathRegexComboSearch($quarterHtml, "//h1[@id='wb-cont']", '/(.*)/');

		// Try to find one of the text labels in the title element, and match the Q1, Q2, etc. value:
		foreach($quarterIndex as $quarterLabel => $quarterKey) {
			if(strpos($title, $quarterLabel) !== false) {
				$fiscalQuarter = $quarterKey;
				break;
			}
		}

		if($output == 'year') {
			return $fiscalYear;
		}
		else {
			return $fiscalQuarter;
		}

	}

	public function fiscalYearFromQuarterPage($quarterHtml, $quarterUrl) {

		return $this->fiscalYearAndQuarterFromTitle($quarterHtml, 'year');

	}

	public function fiscalQuarterFromQuarterPage($quarterHtml, $quarterUrl) {

		return $this->fiscalYearAndQuarterFromTitle($quarterHtml, 'quarter');

	}

	public static function parseHtml($html) {

		$keyArray = [
			'vendorName' => 'Vendor',
			'referenceNumber' => 'Reference Number',
			'contractDate' => 'Contract Date',
			'description' => 'Description of work',
			'extraDescription' => 'Detailed Description',
			'contractPeriodStart' => '',
			'contractPeriodEnd' => '',
			'contractPeriodRange' => 'Contract Period',
			'deliveryDate' => 'Delivery Date',
			'originalValue' => 'Original Contract Value',
			'contractValue' => 'Contract Value',
			'comments' => 'Comments',
		];

		return Helpers::genericXpathParser($html, "//table//th[@scope='row']", "//table//td", ' to ', $keyArray);

	}

}