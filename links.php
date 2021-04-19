<?php
define('URL', 'https://marvel.fandom.com/wiki/Category:%YEAR%,_%MONTH%');
define('TOP', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE xml>\n");
$years = array('1939');
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

foreach ($years as $year) {
   echo "Building year: " . $year . "\n";
   $yearContents = "";

   $monthNum = 1;
   foreach ($months as $month) {
      $url = str_replace(array("%YEAR%", "%MONTH%"), array($year, $month), URL);
      $website = @file_get_contents($url);
      if ($website == '' || $website === false) continue;

      $doc = new DOMDocument();
      libxml_use_internal_errors(true);
      $doc->loadHTML($website); // or you could load from a string using loadHTML();
      libxml_clear_errors();
      $xpath = new DOMXpath($doc);
      $elements = $xpath->query("//div[@class='wikia-gallery-item']//a");

      $sep = "\n";
      $monthContents = "";
      foreach($elements as $elem){
         $class = $elem->getAttribute('class');
         if ($class == '') {
            list($title, $number) = explode('#', $elem->nodeValue);
            $monthContents .= $sep . '            <File Name="' . trim($title) . " " . $number . '"/>';
            $sep = "\n";
         }
      }

      if ($monthContents != "") {
         $yearContents .= '        <Directory Name="CMC ' . $year . ' ' . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . '">' 
                          . $monthContents . "\n        </Directory>\n";
      }

      $monthNum++;
   }

   echo "Saving year: " . $year . "\n";
   if ($yearContents != "") {
      file_put_contents($year . ".xml", TOP . '    <Year Name="CMC ' . $year . "\">\n" . $yearContents . '    </Year>');
   }
}
?>