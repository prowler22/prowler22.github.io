<?php
$newline = $_REQUEST['PUZZLE'];

$filenameBase = "pz-stars-book";
$saveLine = true;

$handle = opendir(".");
if ($handle) {
   while (false !== ($entry = readdir($handle))) {
      if (substr($entry, 0, 13) == $filenameBase) {
         $currentFile = substr($entry , 13);
         $myFile[] = str_replace(".txt", "", $currentFile);

         $fileContents = file_get_contents($entry);

         if (strpos($fileContents, $newline) !== false) {
            $saveLine = false;
            break;
         }
      }
   }
   closedir($handle);
}
if ($saveLine) {
   rsort($myFile);
   $currentBookNum = $myFile[0];

   $f_contents = file($filenameBase . $currentBookNum . ".txt");

   $newlineChar = "\n";

   if (sizeof($f_contents) >= 1000) {
      $currentBookNum++;
      $newlineChar = "";
   }

   file_put_contents($filenameBase . $currentBookNum . ".txt", $newlineChar.$newline, FILE_APPEND);
   echo "Saved";
} else {
   echo "Duplicate";
}
?>