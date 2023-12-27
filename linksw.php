<?php
define('URL', 'http://www.mikesamazingworld.com/mikes/features/newsstand.php?type=calendar&month=%MONTH%&year=%YEAR%&publisher=marvel&sort=date&checklist=on&variantex=on&collectionex=on');
define('DCURL', 'http://www.mikesamazingworld.com/mikes/features/newsstand.php?type=calendar&month=%MONTH%&year=%YEAR%&publisher=dc&sort=date&checklist=on&variantex=on&collectionex=on');
define('TOP', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE xml>\n");
$startYear = 1939;
$startURL = URL;
$dc = "";
$project = "CMC";
if (isset($argv[1]) && strtolower($argv[1]) == "dc") {
   $dc = "dc";
   $startYear = 1935;
   $startURL = DCURL;
   $project = "CDCC";
}
//$years = array('1935');
$years = range($startYear, 2020);

//$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

foreach ($years as $year) {
   echo "Building year: " . $year . "\n";
   $yearContents = "";

   //foreach ($months as $month) {
   for ($month = 1; $month <= 12; $month++) {
      $url = str_replace(array("%YEAR%", "%MONTH%"), array($year, $month), $startURL);
      $website = @file_get_contents($url);
      if ($website == '' || $website === false) continue;

      $doc = new DOMDocument();
      libxml_use_internal_errors(true);
      $doc->loadHTML($website); // or you could load from a string using loadHTML();
      libxml_clear_errors();
      $xpath = new DOMXpath($doc);
      $elements = $xpath->query("//table[@class='covertable']//a");

      $sep = "\n";
      $monthContents = "";
      foreach ($elements as $elem) {
         $class = $elem->getAttribute('class');
         if ($class == '') {
            $href = $elem->getAttribute('href');
            $hrefArr = explode('=', $href);
            $id = "";
            $seriesId = '1';
            if (sizeof($hrefArr) >= 2) $id = $hrefArr[1];

            $websiteissue = @file_get_contents('http://www.mikesamazingworld.com' . $href);
            if ($websiteissue == '' || $websiteissue === false) {
               $seriesId = '0';
            } else {
               $doc2 = new DOMDocument();
               libxml_use_internal_errors(true);
               $doc2->loadHTML($websiteissue); // or you could load from a string using loadHTML();
               libxml_clear_errors();
               $xpath2 = new DOMXpath($doc2);
               $elements2 = $xpath2->query("//li[@id='series']//a");

               foreach ($elements2 as $elem2) {
                  $href2 = $elem2->getAttribute('href');
                  $hrefArr2 = explode('=', $href2);
                  if (sizeof($hrefArr2) >= 2) $seriesId = $hrefArr2[1];
               }
            }

            if (strpos($elem->nodeValue, '#') !== false) {
               list($title, $number) = explode('#', $elem->nodeValue);
               $number = " " . $number;
            } else {
               $title = $elem->nodeValue;
               $number = "";
            }

            $title = str_replace('&', '&amp;', $title);
            $title = str_replace('"', '&quot;', $title);
            $title = str_replace("'", '&apos;', $title);
            $number = str_replace('&', '&amp;', $number);
            $monthContents .= $sep . '            <File Name="' . trim($title) . $number . '" ID="' . $id . '" SERIESID="' . $seriesId . '"/>';
            $sep = "\n";
         }
      }

      if ($monthContents != "") {
         $yearContents .= '        <Directory Name="' . $project . ' ' . $year . ' ' . str_pad($month, 2, '0', STR_PAD_LEFT) . '">'
            . $monthContents . "\n        </Directory>\n";
      }
   }

   echo "Saving year: " . $year . "\n";
   if ($yearContents != "") {
      file_put_contents("xml\weekly" . $dc . $year . ".xml", TOP . '    <Year Name="' . $project . ' ' . $year . "\">\n" . $yearContents . '    </Year>');
   }
}
