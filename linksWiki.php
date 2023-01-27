<?php
//define('URL', 'http://www.mikesamazingworld.com/mikes/features/newsstand.php?type=calendar&month=%MONTH%&year=%YEAR%&publisher=marvel&sort=date&checklist=on&variantex=on&collectionex=on');
define('URL', 'https://marvel.fandom.com/wiki/Category:Week_%WEEK%,_%YEAR%');
//define('DCURL', 'http://www.mikesamazingworld.com/mikes/features/newsstand.php?type=calendar&month=%MONTH%&year=%YEAR%&publisher=dc&sort=date&checklist=on&variantex=on&collectionex=on');
define('TOP', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE xml>\n");
$startYear = 2023;
$startURL = URL;
$dc = "";
$project = "CMC";
if (isset($argv[1]) && strtolower($argv[1]) == "dc") {
   $dc = "dc";
   $startYear = 2023;
   $startURL = DCURL;
   $project = "CDCC";
}
//$years = array('1935');
$years = range($startYear,date("Y"));

//$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

foreach ($years as $year) {
   echo "Building year: " . $year . "\n";
   $yearContents = "";

   //foreach ($months as $month) {
   for ($week = 1; $week <= 53; $week++) {
      $url = str_replace(array("%YEAR%", "%WEEK%"), array($year, str_pad($week, 2, "0", STR_PAD_LEFT)), $startURL);
      $website = @file_get_contents($url);
      if ($website == '' || $website === false) continue;

      $doc = new DOMDocument();
      libxml_use_internal_errors(true);
      $doc->loadHTML($website); // or you could load from a string using loadHTML();
      libxml_clear_errors();
      $xpath = new DOMXpath($doc);
      $elements = $xpath->query("//div[@class='lightbox-caption']//a");

      $sep = "\n";
      $weekContents = "";
      $i = 1;
      foreach($elements as $elem){
         $class = $elem->getAttribute('class');
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
        $weekContents .= $sep . '            <File Name="' . trim($title) . $number . '" ID="' . $year . str_pad($week, 2, '0', STR_PAD_LEFT) . str_pad($i, 2, '0', STR_PAD_LEFT) . '"/>';
        $sep = "\n";
        $i++;
      }

      if ($weekContents != "") {
         $yearContents .= '        <Directory Name="' . $project . ' ' . $year . ' ' . str_pad($week, 2, '0', STR_PAD_LEFT) . '">' 
                          . $weekContents . "\n        </Directory>\n";
      }
   }

   echo "Saving year: " . $year . "\n";
   if ($yearContents != "") {
      file_put_contents("xml\weekly" . $dc . $year . ".xml", TOP . '    <Year Name="' . $project . ' ' . $year . "\">\n" . $yearContents . '    </Year>');
   }
}
?>