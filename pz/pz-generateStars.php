<?php
// Run this first to generate a 1000 star puzzles in the file pz-stars-temp1.txt
// Then open pz-random.php to check if solvable and then save

$size = 5;
$numPuz = 1000;

file_put_contents("pz-stars-temp1.txt", "");

for ($i = 0; $i < $numPuz; $i++) {
   generate($size, $i);
}

exit;

function buildBoard($size, &$map) {
   // First place random start locations for each segment (number of segments is equal to size)
   for ($i = 1; $i <= $size; $i++) {
      $setSeg = false;
      while (!$setSeg) {
         $x = randint($size);
         $y = randint($size);
         $location = map_index($x, $y, $size);
         if ($map[$location] == 0) {
            $map[$location] = $i;
            $setSeg = true;
         }
      }
   }

   while (strpos(implode("", $map), "0") !== false) {
      for ($i=0; $i < sizeof($map); $i++) {
         if ($map[$i] > 0) {
            $randomCell = getAdjacentRandomCell($i, $size);
            if ($randomCell != -1 && $map[$randomCell] == 0) {
               $map[$randomCell] = $map[$i];
            }
         }
      }
   }
}

function map_index($x, $y, $size) {
   return $x + $y * $size;
}

function randint($K) {
   return rand(0, $K - 1);
}

function getAdjacentRandomCell($cell, $size) {
   $left = -1;
   $top = -1;
   $right = -1;
   $bottom = -1;
   $count = 0;
   $randomCell = -1;

   if (($cell % $size) != 0) {
      $left = $cell - 1;
      $count++;
   }

   if ($cell >= $size) {
      $top = $cell - $size;
      $count++;
   }

   if (($cell + 1) % $size != 0) {
      $right = $cell + 1;
      $count++;
   }
    
   if (($cell + $size) < $size * $size) {
      $bottom = $cell + $size;
      $count++;
   }

   if ($count > 0) {
      $i = 0;
      $randomCells = array_fill(0, $count, "0");

      if ($left >= 0) {
         $randomCells[$i] = $left;
         $i++;
      }

      if ($top >= 0) {
         $randomCells[$i] = $top;
         $i++;
      }

      if ($right >= 0) {
         $randomCells[$i] = $right;
         $i++;
      }

      if ($bottom >= 0) {
         $randomCells[$i] = $bottom;
         $i++;
      }

      $randomInt = randint($count);
      $randomCell = $randomCells[$randomInt];
   }

   return $randomCell;
}
/*
function is_touching($A, $B, $S) {
   // function to determine if two points are adjacent
   // this can be changed for star battle variants
   if ($A == $B) {
      return true;
   }
   $xA = $A % $S;
   $yA = Math.floor($A/$S);
   $xB = $B % $S;
   $yB = Math.floor($B/$S);
   $dX = Math.abs($xA-$xB);
   $dY = Math.abs($yA - $yB);
   if ($dX == 1 && $dY <= 1) {
      return true;
   }
   else if ($dY == 1 && $dX <= 1) {
      return true;
   }
   else {
      return false;
   }
}

// Unique elements of an array
// thanks https://stackoverflow.com/a/18678042
function arrayUnique($arr) {
   $a = [];
   for($k=0;$k<sizeof($arr);$k++)
      if(strpos($arr[$k], $a)===false)
         array_push($a, $arr[$k]);

   return sort($a);
}

function star_battle_solver($grid_orig, $num_stars) {
   // `grid_orig` is a string of capital letters
   // rows are separated by newline characters
   // `num_stars` is the number of stars to be filled in each region
   global $TOTAL;
   global $COUNTER_LIMIT;
   global $TIMED_OUT_STRING;
   global $regions;
   global $num_stars;
   $i = 0;
   $j = 0;
   
   // S is the number of rows (the grid is presumed square)
   $S = sizeof(explode($grid_orig, '\n'));
   // LENGTH is the number of squares in the grid
   $LENGTH = $S * $S;
   
   // TOTAL is the eventual number of stars in the grid
   $TOTAL = $num_stars * $S;
   
   // Flatten the grid
   $grid = str_replace('\n', '', $grid_orig);
   
   // Get the unique letters in the grid
   $region_names = arrayUnique(explode('', $grid));
   
   // Possible places for the stars in each region
   $possibles = array();
   for ($j=0; $j<sizeof($region_names); $j++) {
      $p = array();
      $l = $region_names[$j];
      for ($i=0; $i<$LENGTH; $i++) {
         if (substr($grid, $i, 1) == l) {
            array_push($p, $i);
         }
      }
      array_push($possibles, $p);
   }
   // Create `regions` and sort by size
   $regions = array();
   for ($i=0; $i<$S; $i++) {
      array_push($regions, $i);
   }
   $regions = regions.sort(function(a,b){return grid.split(region_names[a]).length - grid.split(region_names[b]).length;});
   
   // Set up the array of adjacent squares in the grid
   $adjacent = array();
   for ($i=0; $i<$LENGTH; $i++) {
      $adj = array();
      for ($j=0; $j<$LENGTH; $j++) {
         if (is_touching($i, $j, $S)) {
            array_push($adj, $j);
         }
      }
      array_push($adjacent, $adj);
   }
   
   // Set up variables for the columns and rows
   $cs = array();
   $cr = array();
   $rs = array();
   for ($i=0; $i<$S; $i++) {
      array_push($rs, 0);
   }
   for ($i=0; $i<$S; $i++) {
      $rs1 = array();
      $cs1 = array();
      for ($j=0; $j<$S; $j++) {
         array_push($rs1, $i*$S + $j);
         array_push($cs1, $j*$S+$i);
         array_push($cr, array($j, $i+$S));
      }
      array_push($cs, $cs1);
      array_push($rs, $rs1);
   }
   
   // In order to not crash anyone's browser, we limit how long we run this for
   $COUNTER = 0;
   $TIMED_OUT_STRING = 'TIMED OUT';
   $COUNTER_LIMIT = 1000000;
   
   // The solver
   function solve($grid, $impossibles, $colrows, $count=0) {
      global $TOTAL;
      global $COUNTER_LIMIT;
      global $TIMED_OUT_STRING;
      global $regions;
      global $num_stars;
      
      //console.log(grid);
      if ($count==$TOTAL) {
         return $grid;
      }
      $COUNTER += 1;
      if ($COUNTER >= $COUNTER_LIMIT) {
         return $TIMED_OUT_STRING;
      }
   
      $region_num = $regions[Math.floor($count/$num_stars)];
      $new_possibles = possibles[region_num].filter(x => ![...impossibles].includes(x) );
      $found = false;
      $s = '';
      for ($k=0; $k<new_possibles.length; k++) {
         var a = new_possibles[k];
         var c = cr[a][0]; var r = cr[a][1];
         var tryImpossibles = new Set([...impossibles, ...adjacent[a]]);
         var tryColRows = colrows.slice(0);
         tryColRows[c] += 1;
         tryColRows[r] += 1;
         if (tryColRows[c] == num_stars) {
            var s1 = new Set(cs[c]);
            tryImpossibles = new Set([...tryImpossibles, ...s1]);
         }
         if (tryColRows[r] == num_stars) {
            var s2 = new Set(rs[r]);
            tryImpossibles = new Set([...tryImpossibles, ...s2]);
         }
         s = solve(grid.concat([a]), tryImpossibles, tryColRows, count+1);
         if (s) {
            found = true;
            break;
         }
      }
      if (!found) {return '';}
      else {return s;}
   }
   
   // Set up the initial conditions and solve
   var colrows1 = [];
   for (i=0; i<S+S; i++) {
      colrows1.push(0);
   }
   var impossibles1 = new Set([]);
   var s = solve([], impossibles1, colrows1);
   
   // Return a value in a readable format
   
   if (s == TIMED_OUT_STRING) {
      return s;
   }
   if (s) {
      var s1 = '';
      for (i=0; i<S; i++) {
         for (j=0; j<S; j++) {
            var mynum = i*S+j;
            if (s.indexOf(mynum) !== -1) {
               s1 += '*';
            }
            else {
               s1 += '.';
            }
         }
         s1 += '\n';
      }
      return s1;
   }
   else {
      return 'NO SOLUTIONS FOUND';
   }
}

*/

function generate($size, $i) {
   $foundValid = false;

   while (!$foundValid) {
      $map = array_fill(0, $size * $size, "0");
      buildBoard($size, $map);
   
      $mapString = implode("", $map);

      $saveMapString = '';
      $finalMapString = '';

      while (strlen($mapString) > 0) {
         $finalMapString .= substr($mapString, 0, $size) . '\n';
         $saveMapString .= substr($mapString, 0, $size) . ',';
         $mapString = substr($mapString, $size);
      }

      $finalMapString = trim($finalMapString);
      $saveMapString = substr($saveMapString, 0, strlen($saveMapString) - 1);
      $newline = "\n";
      if ($i == 0) $newline = "";
      file_put_contents("pz-stars-temp1.txt", $newline.$saveMapString, FILE_APPEND);
//echo $saveMapString;
return $saveMapString;
//      $mySolution = star_battle_solver($finalMapString, 1);

 /*     if (strpos($mySolution, "*") !== false) {
         $saveSolution = "";
         for ($i = 0; $i < strlen($mySolution); $i++) {
            if ($mySolution[i] == ".") $saveSolution .= "0";
            if ($mySolution[i] == "*") $saveSolution .= "1";
         }
         $savePuzzle = $saveMapString + "|" + $saveSolution;

         $foundValid = true;
         file_put_contents("pz-stars-book001.txt", "\n".$savePuzzle, FILE_APPEND);
      }*/
   }
}
?>