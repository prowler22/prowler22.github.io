<?php
define('URL', 'https://leagueofcomicgeeks.com/comics/new-comics/%YEAR%/%MONTH%/%DAY%?publisher=%PUB%');
define('WEEKURL', 'https://getcomics.org/other-comics/%YEAR%-%MONTH%-%DAY%-weekly-pack/');
define('COMICURL', 'https://comic-reader.000webhostapp.com/wr/reader.php?URL=%URL%');
define('TOP', "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<!DOCTYPE xml>\n");

$startYear = 2021;
$startURL = URL;
$dc = "";
$pub = 2;
$project = "CMC";
if (isset($argv[1]) && strtolower($argv[1]) == "dc") {
   $dc = "dc";
   $pub = 1;
   $startYear = 2021;
   $project = "CDCC";
}

$years = range($startYear, date("Y"));

foreach ($years as $year) {
   echo "Building year: " . $year . "\n";
   $yearContents = "";

   $firstDayOfYear = new DateTime($year . "1/1");
   $dayDiff = 0;
   $dayofweek = date('w', strtotime($year . "/1/1"));

   if ($dayofweek < 3) {
      $dayDiff = 3 - $dayofweek;
   } else if ($dayofweek > 3) {
      $dayDiff = 7 - $dayofweek + 3;
   }

   $d = new DateTime();
   $currentWed = new DateTime($year . "/1/" . (1 + $dayDiff));
   $week = 1;
   while (date('Y', $currentWed->getTimestamp()) == $year && $currentWed < $d) {
      $currentYear = date('Y', $currentWed->getTimestamp());
      $currentDay = date('d', $currentWed->getTimestamp());
      $currentMonth = date('m', $currentWed->getTimestamp());
      $nextDate = strtotime("+7 day", $currentWed->getTimestamp());
      $currentWed = new DateTime();
      $currentWed->setTimestamp($nextDate);

      $url = str_replace(array("%YEAR%", "%MONTH%", "%DAY%", "%PUB%"), array($year, $currentMonth, $currentDay, $pub), $startURL);
      $website = @file_get_contents_curl($url);
      if ($website == '' || $website === false) {
         echo "Website not loaded: " . $url;
         continue;
      }

      $doc = new DOMDocument();
      libxml_use_internal_errors(true);
      $doc->loadHTML($website); // or you could load from a string using loadHTML();
      libxml_clear_errors();
      $xpath = new DOMXpath($doc);
      $elements = $xpath->query("//div[@class='title color-primary']//a");

      $sep = "\n";
      $weekContents = "";
      $i = 1;
      $weekList = array();
      foreach ($elements as $elem) {
         $weekList[] = $elem->nodeValue;
      }

      sort($weekList);
      foreach ($weekList as $nextTitle) {
         $link = '';

         if (strpos($nextTitle, '#') !== false) {
            list($title, $number) = explode('#', $nextTitle);
            $number = " " . $number;
         } else {
            $title = $nextTitle;
            $number = "";
         }

         /* echo "A" . $nextTitle . "A\n";
         for ($index = 0; $index < sizeof($titles); $index++) {
            echo "T" . $titles[$index] . "T\n";
            if ($titles[$index] == $title && $number[$index] == $number) {
               $link = str_replace('%URL%', $hrefs[$index], COMICURL);
            }
         }*/
         $title = str_replace('&', '&amp;', $title);
         $title = str_replace('"', '&quot;', $title);
         $title = str_replace("'", '&apos;', $title);
         $number = str_replace('&', '&amp;', $number);
         $weekContents .= $sep . '            <File Name="' . trim($title) . $number . '"/>';
         $sep = "\n";
         $i++;
      }

      $titles = array();
      $hrefs = array();
      get_comic_links($year, $currentMonth, $currentDay, $pub, $titles, $hrefs);

      for ($index = 0; $index < sizeof($titles); $index++) {
         $weekContents .= $sep . '            <Link Href="' . $hrefs[$index] . '" Name="' . $titles[$index] . '"/>';
      }
      if ($weekContents != "") {
         $yearContents .= '        <Directory Month="' . $currentMonth . '" Name="' . $project . ' ' . $year . ' ' . str_pad($week, 2, '0', STR_PAD_LEFT) . '">'
            . $weekContents . "\n        </Directory>\n";
      }
      $week++;
   }

   echo "Saving year: " . $year . "\n";
   if ($yearContents != "") {
      file_put_contents("xmlwiki\weekly" . $dc . $year . ".xml", TOP . '    <Year Name="' . $project . ' ' . $year . "\">\n" . $yearContents . '    </Year>');
   }
}


function file_get_contents_curl($url)
{
   $ch = curl_init();

   curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_URL, $url);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);


   $data = curl_exec($ch);
   curl_close($ch);

   return $data;
}

function get_comic_links($year, $month, $day, $pub, &$titles, &$hrefs)
{
   $url = str_replace(array("%YEAR%", "%MONTH%", "%DAY%", "%PUB%"), array($year, $month, $day, $pub), WEEKURL);
   $website = @file_get_contents_curl($url);
   if ($website == '' || $website === false) {
      echo "ERROR: Website not loaded.";
      return false;
   }

   $doc = new DOMDocument();
   libxml_use_internal_errors(true);
   $doc->loadHTML($website);
   libxml_clear_errors();
   $xpath = new DOMXpath($doc);
   if ($pub == 1) {
      $elements = $xpath->query("//span[contains(.,'DC COMICS')]/parent::h3/following-sibling::ul[1]/li");
   } else {
      $elements = $xpath->query("//span[contains(.,'MARVEL COMICS')]/parent::h3/following-sibling::ul[1]/li");
   }

   $titles = array();
   $hrefs = array();
   foreach ($elements as $elem) {
      list($title) = explode(':', $elem->nodeValue);

      $readLink = $xpath->query(".//a", $elem);
      if ($readLink[1]) {
         $href = $readLink[1]->getAttribute('href');
         $titles[] = trim(str_replace('&', '&amp;', str_replace("–", "-", $title)));
         $hrefs[] = str_replace('%URL%', $href, COMICURL);
      } else {
         $titles[] = $title;
         $hrefs[] = "none";
      }
   }

   array_multisort($titles, $hrefs);
}
