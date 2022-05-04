<?php
//Top-Right-Bottom-Left
/*$stylesArray = array(
  array("BLLB", "BBBL", "BLBB", "BLBL", "BBLL"),
  array("LBBB", "BLBB", "BLBL", "BBLL", "LBBB"),
  array("BBLB", "BLBB", "BBLL", "LBBB", "BBLB"),
  array("LLBB", "BBBL", "LLLB", "BLLL", "LBLL"),
  array("BLBB", "BLBL", "LLBL", "LLBL", "LBBL")
);
$answer = "0010010000000100100000001";
*/

$filenameBase = "pz-stars-book";
$fileNo = '';
if (isset($_REQUEST['BOOK'])) {
   $fileNo = $_REQUEST['BOOK'];
} else {
   $handle = opendir(".");
   if ($handle) {
      while (false !== ($entry = readdir($handle))) {
         if (substr($entry, 0, 13) == $filenameBase) {
            $currentFile = substr($entry , 13);
            $myFile[] = str_replace(".txt", "", $currentFile);
         }
      }
      closedir($handle);
   }
   rsort($myFile);
   $fileNo = rand(1, count($myFile));
}

$f_contents = file("pz-stars-book" . $fileNo . ".txt");

$lineNo = '';
if (isset($_REQUEST['BOOK'])) {
   $lineNo = $_REQUEST['PUZZLE'] - 1;
} else {
   $lineNo = rand(0, count($f_contents) - 1);
}

$line = $f_contents[$lineNo];
$titleText = "Book " . $fileNo . " Puzzle " . ($lineNo + 1);

$explodeLine = explode("|", $line); 
$answer = $explodeLine[1];

$rows = explode(",", $explodeLine[0]);
$rowNum = 0;
$stylesArray = array();
$rowsArray = array();

foreach ($rows as $row) {
   $rowArray = array();
   for ($i = 0; $i < strlen($row); $i++) {
      $rowArray[$i] = $row[$i];
   }
   $rowsArray[$rowNum] = $rowArray;
   $rowNum++;
}

$rowNum = 0;
foreach ($rowsArray as $row) {
   $styleRow = array();

   for ($i = 0; $i < sizeof($row); $i++) {
      $cell = "";

      // if this is the top row or the number above is different set to a bold line
      if ($rowNum == 0 || $rowsArray[$rowNum][$i] != $rowsArray[$rowNum - 1][$i]) {
         $cell .= "B";
      } else {
         $cell .= "L";
      }

      // if this is the right column or the number to the right is different set to a bold line
      if ($i == sizeof($row) - 1 || $rowsArray[$rowNum][$i] != $rowsArray[$rowNum][$i + 1]) {
         $cell .= "B";
      } else {
         $cell .= "L";
      }

      // if this is the bottom row or the number below is different set to a bold line
      if ($rowNum == sizeof($rowsArray) - 1 || $rowsArray[$rowNum][$i] != $rowsArray[$rowNum + 1][$i]) {
         $cell .= "B";
      } else {
         $cell .= "L";
      }

      // if this is the left column or the number to the left is different set to a bold line
      if ($i == 0 || $rowsArray[$rowNum][$i] != $rowsArray[$rowNum][$i - 1]) {
         $cell .= "B";
      } else {
         $cell .= "L";
      }
      $styleRow[$i] = $cell;
   }
   $stylesArray[$rowNum] = $styleRow;
   $rowNum++;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Star Battle</title>
<style type="text/css">
table.grid {
   border-spacing: 15px;
   border: 1px solid blue;
   margin-left: auto;
   margin-right: auto;
}
table.cell {
   border-collapse: collapse;
}
td.cell {
   border-collapse: collapse;
   border-color: black;
   border-style: solid;
   border-spacing: 0px;
   height:30px;
   width:30px;
   text-align:center;
}
</style>
<script type="text/javascript" src="lib/js/jquery-3.5.1.min.js"></script>
<script>
var solution = "<?php echo trim($answer);?>";
function clickCell(tblCell, event) {
   if (event.which == 1) {
      if (tblCell.innerText == "*") {
         tblCell.innerText = "";
      } else {
         tblCell.innerText = "*";
      }
   } else if (event.which == 3) {
      if (tblCell.innerText == "X") {
         tblCell.innerText = "";
      } else {
         tblCell.innerText = "X";
      }
   }
   checkPuzzle(false);
   return false;
}
function checkPuzzle(showErrors) {
   var userAnswer = "";
   var table = document.getElementById("puzzlegrid");
   for (var i = 0, row; row = table.rows[i]; i++) {
      for (var j = 0, col; col = row.cells[j]; j++) {
         if (col.innerText == "*") {
            userAnswer += "1";
         } else {
            userAnswer += "0";
         }
      }
   }

   var correct = false;
   if (userAnswer == solution) {
      correct = true;
   } else {
      // if the user answer doesn't match the saved answer, check to see if they found another solution
      var rowsValid = true;
      var colsValid = true;
      var segmentsValid = true;

      for (var i=0; i < table.rows.length; i++) {
         if (!checkItems("rowNum", i)) rowsValid = false;
         if (!checkItems("colNum", i)) colsValid = false;
         if (!checkItems("segment", i + 1)) segmentsValid = false;
      }

      if (rowsValid && colsValid && segmentsValid) {
         var placementValid = true;
         for (var i = 0, row; row = table.rows[i]; i++) {
            for (var j = 0, col; col = row.cells[j]; j++) {
               if (col.innerText == "*") {
                  // row - 1 col -1, row -1 col, row -1 col + 1
                  // row col -1, row col + 1
                  // row + 1 col -1, row +1 col, row + 1 col + 1

                  var colToCheck;

                  // check the three cells in the above row that surrond this cell
                  if (i > 0) {
                     var rowAbove = table.rows[i - 1];
                     if (j > 0) {
                        colToCheck = rowAbove.cells[j - 1];
                        if (colToCheck.innerText == "*") placementValid = false;
                     }

                     colToCheck = rowAbove.cells[j];
                     if (colToCheck.innerText == "*") placementValid = false;

                     if (j + 1 < rowAbove.cells.length) {
                        colToCheck = rowAbove.cells[j + 1];
                        if (colToCheck.innerText == "*") placementValid = false;
                     }
                  }

                  // check the two cells in the same row that surrond this cell
                  var rowCurrent = table.rows[i];
                  if (j > 0) {
                     colToCheck = rowCurrent.cells[j - 1];
                     if (colToCheck.innerText == "*") placementValid = false;
                  }

                  if (j + 1 < rowCurrent.cells.length) {
                     colToCheck = rowCurrent.cells[j + 1];
                     if (colToCheck.innerText == "*") placementValid = false;
                  }

                  // check the three cells in the below row that surrond this cell
                  if (i + 1 < table.rows.length) {
                     var rowBelow = table.rows[i + 1];
                     if (j > 0) {
                        colToCheck = rowBelow.cells[j - 1];
                        if (colToCheck.innerText == "*") placementValid = false;
                     }

                     colToCheck = rowBelow.cells[j];
                     if (colToCheck.innerText == "*") placementValid = false;

                     if (j + 1 < rowBelow.cells.length) {
                        colToCheck = rowBelow.cells[j + 1];
                        if (colToCheck.innerText == "*") placementValid = false;
                     }
                  }
               }
            }
         }

         if (placementValid) {
            correct = true;
         }
      }
   }

   if (correct) {
      alert("You got the puzzle correct!");
   } else {
      if (showErrors)
         alert("There seems to be some mistakes.");
   }
}

function checkItems(selector, i) {
   var valid = true;
   var items = $("td[" + selector + "='" + i + "']");
   var count = 0;

   $(items).each(function() {
      if ($(this).text() == "*") count++;
   });

   if (count != 1) valid = false;
   return valid;

}
</script>
</head>
<body oncontextmenu="return false;">
   <table class="grid">
      <tr>
         <td><div id="puzzleId" style="text-align:center;"><?=$titleText?></div></td>
      </tr>
      <tr>
         <td>
            <table class="cell" id="puzzlegrid">
            <?php
            $rowNum = 0;
            foreach ($stylesArray as $rowStyle) {
               echo "<tr>";
               $colNum = 0;
               foreach ($rowStyle as $style) {
                  echo "<td segment='".$rowsArray[$rowNum][$colNum] . "' rowNum='". $rowNum . "' colNum='". $colNum . "' onmousedown='return clickCell(this, event)' class='cell' style='border-spacing:0px;border-width:";
                  for ($i = 0; $i<strlen($style); $i++) {
                     $borderWidth = ($style[$i] == "B") ? "3" : "1";
                     echo " " . $borderWidth . "px";
                  }
                  echo ";'></td>\n";
                  $colNum++;
               }
               echo "</tr>";
               $rowNum++;
            }
            ?>
            </table> 
         </td>
      </tr>
      <tr>
         <td>
            <input id="checkPuzzle" type="button" value="Check Puzzle" onclick="checkPuzzle(true);">
            <input id="newPuzzle" type="button" value="New Puzzle" onclick="location.reload();">
         </td>
      </tr>
   </table>
</body>
</html>