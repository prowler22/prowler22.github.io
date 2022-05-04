/**
 * This is a port of the Python code from
 * https://www.reddit.com/r/dailyprogrammer/comments/7xyi2w/20180216_challenge_351_hard_star_battle_solver/dugbb1e/
 * Released under the MIT license
 * https://opensource.org/licenses/MIT
**/

function is_touching(A, B, S) {
    // function to determine if two points are adjacent
    // this can be changed for star battle variants
    if (A == B) {
        return true;
    }
    xA = A % S; yA = Math.floor(A/S);
    xB = B % S; yB = Math.floor(B/S);
    dX = Math.abs(xA-xB); dY = Math.abs(yA - yB);
    if (dX == 1 && dY <= 1) {
        return true;
    }
    else if (dY == 1 && dX <= 1) {
        return true;
    }
    else {
        return false;
    }
}

// Unique elements of an array
// thanks https://stackoverflow.com/a/18678042
var arrayUnique = function(arr) {
    var a = [];
    for(var k=0;k<arr.length;k++)
        if(a.indexOf(arr[k])==-1)
             a.push(arr[k]);
    return a.sort();
};

function star_battle_solver(grid_orig, num_stars) {
    // `grid_orig` is a string of capital letters
    // rows are separated by newline characters
    // `num_stars` is the number of stars to be filled in each region
    
    var i,j;
    
    // S is the number of rows (the grid is presumed square)
    var S = grid_orig.split('\n').length;
    // LENGTH is the number of squares in the grid
    var LENGTH = S * S;
    
    // TOTAL is the eventual number of stars in the grid
    var TOTAL = num_stars * S;
    
    // Flatten the grid
    var grid = grid_orig.replace(/\n/g, '');
    
    // Get the unique letters in the grid
    var region_names = arrayUnique(grid.split(''));
    
    // Possible places for the stars in each region
    var possibles = [];
    for (j=0; j<region_names.length; j++) {
        var p = [];
        l = region_names[j];
        for (i=0; i<LENGTH; i++) {
            if (grid.charAt(i) == l) {
                p.push(i);
            }
        }
        possibles.push(p);
    }
    // Create `regions` and sort by size
    var regions = [];
    for (i=0; i<S; i++) {
        regions.push(i);
    }
    regions = regions.sort(function(a,b){return grid.split(region_names[a]).length - grid.split(region_names[b]).length;});
    
    // Set up the array of adjacent squares in the grid
    var adjacent = [];
    for (i=0; i<LENGTH; i++) {
        var adj = [];
        for (j=0; j<LENGTH; j++) {
            if (is_touching(i, j, S)) {
                adj.push(j);
            }
        }
        adjacent.push(adj);
    }
    
    // Set up variables for the columns and rows
    var cs = [], cr = [], rs = [];
    for (i=0; i<S; i++) {
        rs.push(0);
    }
    for (i=0; i<S; i++) {
        var rs1 = [], cs1 = [];
        for (j=0; j<S; j++) {
            rs1.push(i*S + j);
            cs1.push(j*S+i);
            cr.push([j, i+S]);
        }
        cs.push(cs1); rs.push(rs1);
    }
    
    // In order to not crash anyone's browser, we limit how long we run this for
    var COUNTER = 0;
    var TIMED_OUT_STRING = 'TIMED OUT';
    var COUNTER_LIMIT = 1000000;
    
    // The solver
    function solve(grid, impossibles, colrows, count=0) {
        //console.log(grid);
        if (count==TOTAL) {
            return grid;
        }
        COUNTER += 1;
        if (COUNTER >= COUNTER_LIMIT) {
            return TIMED_OUT_STRING;
        }
        
        var region_num = regions[Math.floor(count/num_stars)];
        var new_possibles = possibles[region_num].filter(x => ![...impossibles].includes(x) );
        var found = false, s = '';
        for (var k=0; k<new_possibles.length; k++) {
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