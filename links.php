<?php
$doc = new DOMDocument();
$doc->loadHTMLFile("https://marvel.fandom.com/wiki/Category:1960,_January"); // or you could load from a string using loadHTML();
$xpath = new DOMXpath($doc);
$elements = $xpath->query("//div[@id='gallery-1']//a");
foreach($elements as $elem){
    echo $elem->getAttribute('class');
}
?>