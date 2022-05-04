<?php
$f_contents = file("pz-stars-temp1.txt");

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="ISO-8859-1">
<title>Insert title here</title>
<script type="text/javascript" src=starBattle2.js></script>
<script>
var fileArr = [];
<?php foreach ($f_contents as $line) :?>
fileArr.push('<?php echo trim($line)?>');
<?php endforeach;?>
</script>
<script>
var currentLine = 0;
var size = 5;
var savedLines = 0;
var duplicateLines = 0;
var unsolvableLines = 0;
var runOutputObj;

function generate() {
   runOutputObj = document.getElementById("runOutput");

   currentLine = 0;
   savedLines = 0;
   duplicateLines = 0;
   unsolvableLines = 0;
   document.getElementById('generate').disabled = true

   runOutputObj.value = "Saving Puzzles...";
   saveLine();
   /*for (var j = 0; j < fileArr.length; j++) {
      var mapString = fileArr[j];
      mapString = mapString.replace(/\,/g, "");
      var saveMapString = '';
      var finalMapString = '';
      while (mapString.length > 0) {
         finalMapString += mapString.substring(0, size) + '\n';
         saveMapString += mapString.substring(0, size) + ',';
         mapString = mapString.substring(size);
      }
      finalMapString = finalMapString.trim();
      saveMapString = saveMapString.substring(0, saveMapString.length - 1);

      var mySolution = star_battle_solver(finalMapString, Number(1));
  
      if (mySolution.includes("*")) {
         var saveSolution = "";
         count++;
         for (var i = 0; i < mySolution.length; i++) {
            if (mySolution[i] == ".") saveSolution += "0";
            if (mySolution[i] == "*") saveSolution += "1";
         }
         var savePuzzle = saveMapString + "|" + saveSolution;

         var xhttp = new XMLHttpRequest();
         xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
               var i = 0;
            }
         };

         xhttp.open("GET", "pz-stars-save.php?PUZZLE=" + savePuzzle, true);
         xhttp.send();
      }
   }
   alert(count + " new puzzles saved");*/
}

function saveLine() {
   if (currentLine >= fileArr.length) {
      alert(savedLines + " new puzzles saved");
      runOutputObj.value += "\n" + savedLines + " new puzzles saved";
      runOutputObj.value += "\n" + duplicateLines + " duplicate puzzles not saved";
      runOutputObj.value += "\n" + unsolvableLines + " unsolvable puzzles not saved";

      runOutputObj.scrollTop = runOutputObj.scrollHeight;
      return;
   }

   runOutputObj.value += "\nSaving Puzzle " + currentLine + "... ";
   runOutputObj.scrollTop = runOutputObj.scrollHeight;
   var mapString = fileArr[currentLine];
   mapString = mapString.replace(/\,/g, "");
   var saveMapString = '';
   var finalMapString = '';
   while (mapString.length > 0) {
      finalMapString += mapString.substring(0, size) + '\n';
      saveMapString += mapString.substring(0, size) + ',';
      mapString = mapString.substring(size);
   }
   finalMapString = finalMapString.trim();
   saveMapString = saveMapString.substring(0, saveMapString.length - 1);

   var mySolution = star_battle_solver(finalMapString, Number(1));

   if (mySolution.includes("*")) {
      var saveSolution = "";
      savedLines++;
      for (var i = 0; i < mySolution.length; i++) {
         if (mySolution[i] == ".") saveSolution += "0";
         if (mySolution[i] == "*") saveSolution += "1";
      }
      var savePuzzle = saveMapString + "|" + saveSolution;

      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function() {
         if (this.readyState == 4 && this.status == 200) {
            currentLine++;
            runOutputObj.value += this.responseText;
            if (this.responseText == "Duplicate") {
               savedLines--;
               duplicateLines++;
            }
            saveLine();
         }
      };

      xhttp.open("GET", "pz-stars-save.php?PUZZLE=" + savePuzzle, true);
      xhttp.send();
   } else {
      runOutputObj.value += "Unsolvable";
      unsolvableLines++;
      currentLine++;
      saveLine();
   }
}
</script>
</head>
<body>
<input id='generate' value='Check and Save' type='button' onclick="generate();">
<br>
<label for="runOutput">Run Output:</label><br><textarea id="runOutput" rows="10" cols="100"></textarea>
</body>
</html>