<?php 
//Initialize x & y coordinate arrays for Theseus & The Minotaur.
$theseusPos = array();
$minotaurPos = array();

$theseus = 60;
$minotaur = 73;
$xmax = 15;
$ymax = 9;
$exit = 29;
$squarepx = 24;//The size of each square in the output .GIF, in pixels.
$timeStep = 1;
$winFlag = 0;
$bail = 0;
$theseusPath = array();
$theseusPath[0] = $theseus;
$minotaurPath = array();
$minotaurPath[0] = $minotaur;
$minotaurTMP = 0;
$deadEnd = 0;
$winTPath = array();
$winMPath = array();
$gameState = array();
array_push($gameState, array($theseus, $minotaur));	

//Build the initial stateMap, where the only known bad board positions are where The Minotaur is on the same square as Theseus.
$stateMap = array();
$TMParray = array();
for ($y = 0; $y < $xmax*$ymax; $y++) {
	array_push($TMParray, "C");
}
for ($x = 0; $x < $xmax*$ymax; $x++) {
	array_push($stateMap, $TMParray);
}
for ($andy = 0; $andy < $xmax*$ymax; $andy++) {
	$stateMap[$andy][$andy] = 1;
}
$stateMap[$theseus][$minotaur] = $timeStep;
$theseusPath[1] = $theseus;
$minotaurPath[1] = $minotaur;

$connections = array();
for ($andy=0; $andy<($xmax*$ymax);$andy++) {
	${"conArray".$andy} = array();
}
$connectionsTMP = array();
ConnectionsArray();

foreach($connectionsTMP as $connection) {
	array_push(${"conArray".$connection['0']}, $connection['1']);
}
for ($andy=0; $andy<($xmax*$ymax);$andy++) {
	array_push($connections, ${"conArray".$andy});
}	

//Run the main loop.
while ($timeStep > 0 && $bail < 100000) {
	$gameState[$timeStep][0] = $theseus;
	$gameState[$timeStep][1] = $minotaur;
	$timeStep += 1;
	if ($winLength && ($timeStep > $winLength)) {		
			$deadEnd = 2;
	} else {
	//If a move that Theseus can make leads to a game state that has not yet occured, move Theseus there.
	if (($stateMap[$connections[$theseus][0]][$minotaur] === "C" || $stateMap[$connections[$theseus][0]][$minotaur] > $timeStep+1) && $connections[$theseus][0] <> $minotaur) {	
		$theseus = intval($connections[$theseus][0]);	
	} elseif (($stateMap[$connections[$theseus][1]][$minotaur] === "C" || $stateMap[$connections[$theseus][1]][$minotaur] > $timeStep+1) && $connections[$theseus][1] <> $minotaur) {
		$theseus = intval($connections[$theseus][1]);
	} elseif (($stateMap[$connections[$theseus][2]][$minotaur] === "C" || $stateMap[$connections[$theseus][2]][$minotaur] > $timeStep+1) && $connections[$theseus][2] <> $minotaur) {
		$theseus = intval($connections[$theseus][2]);
	} elseif (($stateMap[$connections[$theseus][3]][$minotaur] === "C" || $stateMap[$connections[$theseus][3]][$minotaur] > $timeStep+1) && $connections[$theseus][3] <> $minotaur) {
		$theseus = intval($connections[$theseus][3]);
	} elseif (($stateMap[$theseus][moveMinotaur($theseus)]) === "C" || ($stateMap[$theseus][moveMinotaur($theseus)]) > $timeStep+1) {	
		//If no available move leads to an untried game state, but standing still does (the Minotaur moves, leading to a previously untried game state), just stand still. 
	} else {
		//If neither moving nor standing still leads to an untried game state, this is an unproductive branch in the maze.  Back up and try a new branch.
		$deadEnd = 2;	
	}		
	$stateMap[$theseus][$minotaur] = $timeStep;
	$minotaurTMP = moveMinotaur($theseus);
	if ($minotaurTMP == $theseus) {
		//If The Minotaur has caught Theseus, back up and try a new route.
		if ($deadEnd == 0) {
			$deadEnd = 1;
		}		
	} else {		
		if ($theseus == $exit) {
			$winFlag = 1;
			if ($winLength) {
				if ($timeStep < $winLength) {
					$theseusPath[$timeStep] = $theseus;
					$minotaurPath[$timeStep] = $minotaur;
					$theseusPath = array_slice($theseusPath, 0, $timeStep+1);
					$minotaurPath = array_slice($minotaurPath, 0, $timeStep+1);
					$winTPath = $theseusPath;
					$winMPath = $minotaurPath;
					$winLength = count($winTPath);	
					
				}
				$deadEnd = 2;
			} else {
				$theseusPath[$timeStep] = $theseus;
				$minotaurPath[$timeStep] = $minotaur;
				$theseusPath = array_slice($theseusPath, 0, $timeStep+1);
				$minotaurPath = array_slice($minotaurPath, 0, $timeStep+1);
				$winTPath = $theseusPath;
				$winMPath = $minotaurPath;
				$winLength = count($winTPath);					
				$deadEnd = 2;
			}
		}
		$minotaur = $minotaurTMP;
	}
	}
	$bail += 1;
	//Rewind time if either the Minotaur kills Theseus ($deadEnd == 1) or no connected spaces in the State Map haven't already been tried ($deadEnd == 2).
	if ($deadEnd > 0) {
		$rewindTime = $gameState[$timeStep-$deadEnd][1];
		$stateMap[$theseus][$rewindTime] = "$timeStep";	
		$timeStep -= $deadEnd;
		$theseus = $gameState[$timeStep][0];
		$minotaur = $gameState[$timeStep][1];
		$deadEnd = 0;
	}		
	$theseusPath[$timeStep] = $theseus;
	$minotaurPath[$timeStep] = $minotaur;
}

//Collate the solution, and generate the HTML table that presents it.
$tmPath = array();
for ($andy = 1; $andy < $winLength; $andy++){
	$tmPath[$andy-1][0] = $winTPath[$andy];
	$tmPath[$andy-1][1] = $winMPath[$andy];
	$tableString = $tableString . "<tr><td>$andy</td><td>".$tmPath[$andy-1][0]."</td><td>".$tmPath[$andy-1][1]."</td></tr>";	
}

makeMazeImage($connectionsTMP);
makeAnimation($tmPath);
makeWebsite($tableString);



function moveMinotaur($theseus) {
	global $minotaur, $xmax, $connections;
	$theseusPos[0] = ($theseus) % $xmax;
	$theseusPos[1] = intval($theseus / $xmax);
	$minotaurTMP = $minotaur;
	//Give The Minotaur two opportunities to move.
	for ($andy = 1; $andy <= 2; $andy++) {	
		$moved = 0;
		$minotaurPos[0] = ($minotaurTMP) % $xmax;
		$minotaurPos[1] = intval($minotaurTMP / $xmax);	
		//Check for & execute any available horizontal move that brings The Minotaur closer to Theseus.
		if ($minotaurPos[0] < $theseusPos[0]) {
			foreach ($connections[$minotaurTMP] as $sq2) {
				if ($minotaurTMP - $sq2 == -1) {
					$minotaurTMP = $sq2;
					$moved = 1;
				}
			}
		} elseif ($minotaurPos[0] > $theseusPos[0]) {
			foreach ($connections[$minotaurTMP] as $sq2) {
				if ($minotaurTMP - $sq2 == 1) {
					$minotaurTMP = $sq2;
					$moved = 1;
				}
			}
		} 
		//If no horizontal move was found, check for & execute any available vertical move that brings The Minotaur closer to Theseus.
		if ($moved == 0) {
			if ($minotaurPos[1] < $theseusPos[1]) {
				foreach ($connections[$minotaurTMP] as $sq2) {
					if ($minotaurTMP - $sq2 == (-1*$xmax)) {
						$minotaurTMP = $sq2;
					}
				}
			} elseif ($minotaurPos[1] > $theseusPos[1]) {
				foreach ($connections[$minotaurTMP] as $sq2) {
					if ($minotaurTMP - $sq2 == $xmax) {
						$minotaurTMP = $sq2;
					}
				}
			}
		}
	}
	return $minotaurTMP;	
}

function makeMazeImage($connectionsTMP) {
	//Build image of maze, with square numbers.
	
	global $xmax, $ymax, $squarepx;	
	
	$xpx = ($squarepx * $xmax)+3;//image size
	$ypx = ($squarepx * $ymax)+3;

	//Build a blank grid image.
	$image = new imagick();
	$image->newImage($xpx, $ypx, "black", "png");
	$image->quantizeImage(3,Imagick::COLORSPACE_RGB,1,false,false);
	$draw = new ImagickDraw(); 
	$draw->setFillColor('white');
	for ($andy = 0; $andy < $xmax; $andy++) {
		for ($billy = 0; $billy < $ymax; $billy++) {
			$draw->rectangle(($andy*$squarepx) + 2, ($billy*$squarepx) + 2, ($andy*$squarepx) + $squarepx, ($billy*$squarepx) + $squarepx);
		}
	}
	$image->drawImage($draw);

	//Remove walls between connected squares.
	$draw = new ImagickDraw(); 
	$draw->setFillColor('light blue');
	foreach($connectionsTMP as $connection) {
		if (abs($connection['0'] - $connection['1']) == 1) {
			//horizontal connection; vertical line
			$square = max($connection['0'], $connection['1']);
			$squarex = ($square) % $xmax;
			$squarey = intval($square / $xmax);	
			$draw->rectangle(($squarex*$squarepx)+1, ($squarey*$squarepx)+2, ($squarex * $squarepx)+1, ($squarey * $squarepx)+$squarepx);
		} else {
			//vertical connection; horizontal line
			$square = max($connection['0'], $connection['1']);
			$squarex = ($square) % $xmax;
			$squarey = intval($square / $xmax);	
			$draw->rectangle(($squarex*$squarepx)+2, ($squarey*$squarepx)+1, ($squarex * $squarepx)+$squarepx, ($squarey * $squarepx)+1);
		}
	}
	$image->drawImage($draw);

	//Add numbers to squares.
	$draw->setFillColor('light blue');
	$draw->setFontSize( 12 );
	for ($andy = 0; $andy < $xmax * $ymax; $andy++) {
		$x = $andy % $xmax;
		$y = intval($andy / $xmax);
		$image->annotateImage($draw, $x*$squarepx+4, $y*$squarepx+14, 0, $andy);
	}
	$image->writeImage('mapnumbers.png');
}

function makeAnimation($tmPath) {
	//Animate each frame of the solution path.
	global $xmax, $ymax, $squarepx;
	$image = new imagick('mapnumbers.png');
	$GIF = new Imagick();
	$GIF->setFormat("gif");
	foreach ($tmPath as $step) {
		$frame = new Imagick('mapnumbers.png');
		$frame->quantizeImage(3,Imagick::COLORSPACE_RGB,1,false,false);
		$frame->setImageDelay(30);
		$theseus =	$step[0];
		$minotaur = $step[1];
		$theseusx = ($theseus) % $xmax;	
		$theseusy = intval($theseus / $xmax);	
		$minotaurx = ($minotaur) % $xmax;
		$minotaury = intval($minotaur / $xmax);
		$draw = new ImagickDraw(); 
		$draw->setFillColor('blue');
		$draw->rectangle(($theseusx*$squarepx) + 5, ($theseusy*$squarepx) + 5, ($theseusx*$squarepx) + ($squarepx - 3), ($theseusy*$squarepx) + ($squarepx - 3));
		$draw->setFillColor('red');
		$draw->rectangle(($minotaurx*$squarepx) + 5, ($minotaury*$squarepx) + 5, ($minotaurx*$squarepx) + ($squarepx - 3), ($minotaury*$squarepx) + ($squarepx - 3));
		$frame->drawImage($draw);
		$GIF->addImage($frame);	
	}
	$GIF->writeImages('solution.gif', true);
}

function makeWebsite($tableString) {
	//Present the solution in HTML.
	$file = fopen("solution.html", "w");
	fwrite($file,"<html>
	<style>
		body{font-family:'Courier New', Courier, monospace;} 
	</style>
	<head>
		<title>Theseus and the Minotaur</title>
	</head>
	<body>
	<h1>Theseus and the Minotaur</h1>	
	<p>Theseus and the Minotaur mazes (invented by Robert Abbott, as presented in his book Mad Mazes) work according to the following rules:</p>
	<p>You are placed into a maze with the Minotaur.  For every step you (Theseus) take the Minotaur gets to take up to two steps, obeying the following program for each step:</p>
	<p>1. If possible, move one horizontal square closer to the player.</p>
	<p>2. If no available horizontal moves bring the Minotaur closer to the player, if possible, move one vertical square closer to the player.</p>
	<p>If the Minotaur catches you, you lose.</p>
	<p>I've written a general solver for Theseus and the Minotaur style problems.  My php code finds the shortest maze solution, generates an animated .GIF of that solution, and outputs this web page presenting that solution.  The code can be found here: <a href='https://github.com/dericmiller/theseus'>https://github.com/dericmiller/theseus</a></p> 	
	<p>Below, I present the program's solution to Abbott's original Theseus and the Minotaur Maze, Mad Maze #20.  In the .GIF, the blue square represents Theseus; the red square represents the Minotaur.</p>
	<p>The Solution - Theseus & The Minotaur's Paths:</p>
	<IMG SRC='solution.gif' TITLE='The Solution Path' ALT='The Solution Path'>	
	<table><tr><td>Step#</td><td>Theseus</td><td>Minotaur</td></tr>".$tableString."</table></body></html>");
	fclose($file);
}

function ConnectionsArray() {
//The connections array records which of the adjacent squares of the maze are connected.
$connectionsTMP2 = array(
	array(0, 1),
	array(1, 0),
	array(1, 2),
	array(2, 1),
	array(2, 3),
	array(2, 17),
	array(3, 2),
	array(3, 4),
	array(3, 18),
	array(4, 3),
	array(4, 19),
	array(5, 6),
	array(5, 20),
	array(6, 5),
	array(6, 7),
	array(7, 6),
	array(7, 8),
	array(8, 7),
	array(9, 10),
	array(10, 9),
	array(10, 11),
	array(10, 25),
	array(11, 10),
	array(11, 12),
	array(12, 11),
	array(12, 13),
	array(13, 12),
	array(13, 28),
	array(15, 30),
	array(16, 31),
	array(17, 2),
	array(17, 32),
	array(18, 3),
	array(19, 4),
	array(19, 34),
	array(20, 5),
	array(20, 35),
	array(21, 22),
	array(22, 7),
	array(22, 21),
	array(22, 23),
	array(22, 37),
	array(23, 22),
	array(23, 24),
	array(24, 23),
	array(24, 25),
	array(25, 10),
	array(25, 24),
	array(26, 27),
	array(27, 26),
	array(27, 28),
	array(28, 13),
	array(28, 27),
	array(28, 29),
	array(28, 43),
	array(29, 28),
	array(30, 15),
	array(30, 31),
	array(30, 45),
	array(31, 16),
	array(31, 30),
	array(31, 32),
	array(31, 46),
	array(32, 17),
	array(32, 31),
	array(32, 33),
	array(33, 32),
	array(33, 34),
	array(33, 48),
	array(34, 19),
	array(34, 33),
	array(34, 35),
	array(34, 49),
	array(35, 20),
	array(35, 34),
	array(35, 36),
	array(35, 50),
	array(36, 35),
	array(36, 37),
	array(37, 22),
	array(37, 36),
	array(37, 38),
	array(37, 52),
	array(38, 37),
	array(38, 39),
	array(38, 53),
	array(39, 38),
	array(39, 40),
	array(39, 54),
	array(40, 39),
	array(40, 41),
	array(41, 40),
	array(41, 42),
	array(42, 41),
	array(42, 43),
	array(42, 57),
	array(43, 28),
	array(43, 42),
	array(43, 58),
	array(45, 30),
	array(45, 60),
	array(46, 31),
	array(46, 61),
	array(47, 48),
	array(48, 33),
	array(48, 47),
	array(48, 63),
	array(49, 34),
	array(49, 64),
	array(50, 35),
	array(50, 51),
	array(50, 65),
	array(51, 50),
	array(51, 52),
	array(51, 66),
	array(52, 37),
	array(52, 51),
	array(52, 67),
	array(53, 38),
	array(53, 68),
	array(54, 39),
	array(54, 55),
	array(55, 54),
	array(55, 56),
	array(56, 55),
	array(56, 57),
	array(57, 42),
	array(57, 56),
	array(57, 72),
	array(58, 43),
	array(58, 73),
	array(60, 45),
	array(60, 75),
	array(61, 46),
	array(61, 76),
	array(62, 63),
	array(62, 77),
	array(63, 48),
	array(63, 62),
	array(63, 78),
	array(64, 49),
	array(64, 79),
	array(65, 50),
	array(65, 80),
	array(66, 51),
	array(66, 81),
	array(67, 52),
	array(67, 82),
	array(68, 53),
	array(68, 69),
	array(68, 83),
	array(69, 68),
	array(69, 70),
	array(70, 69),
	array(70, 71),
	array(71, 70),
	array(71, 86),
	array(72, 57),
	array(72, 87),
	array(73, 58),
	array(73, 88),
	array(75, 60),
	array(75, 90),
	array(76, 61),
	array(76, 91),
	array(77, 62),
	array(77, 92),
	array(78, 63),
	array(78, 79),
	array(78, 93),
	array(79, 64),
	array(79, 78),
	array(79, 94),
	array(80, 65),
	array(80, 95),
	array(81, 66),
	array(81, 82),
	array(81, 96),
	array(82, 67),
	array(82, 81),
	array(82, 83),
	array(82, 97),
	array(83, 68),
	array(83, 82),
	array(83, 84),
	array(83, 98),
	array(84, 83),
	array(84, 85),
	array(85, 84),
	array(85, 86),
	array(86, 71),
	array(86, 85),
	array(86, 101),
	array(87, 72),
	array(87, 102),
	array(88, 73),
	array(88, 103),
	array(90, 75),
	array(90, 105),
	array(91, 76),
	array(91, 106),
	array(92, 77),
	array(92, 107),
	array(93, 78),
	array(93, 108),
	array(94, 79),
	array(94, 109),
	array(95, 80),
	array(95, 110),
	array(96, 81),
	array(96, 111),
	array(97, 82),
	array(97, 112),
	array(98, 83),
	array(98, 113),
	array(99, 98),
	array(99, 100),
	array(100, 99),
	array(100, 101),
	array(101, 86),
	array(101, 100),
	array(101, 116),
	array(102, 87),
	array(102, 117),
	array(103, 88),
	array(103, 118),
	array(105, 90),
	array(105, 120),
	array(106, 91),
	array(106, 121),
	array(107, 92),
	array(107, 122),
	array(108, 93),
	array(108, 123),
	array(109, 94),
	array(109, 124),
	array(110, 95),
	array(110, 125),
	array(111, 96),
	array(111, 126),
	array(112, 97),
	array(112, 127),
	array(113, 98),
	array(113, 128),
	array(114, 113),
	array(114, 115),
	array(115, 114),
	array(115, 116),
	array(116, 101),
	array(116, 115),
	array(116, 131),
	array(117, 102),
	array(117, 132),
	array(118, 103),
	array(118, 133),
	array(120, 105),
	array(120, 121),
	array(121, 106),
	array(121, 120),
	array(121, 122),
	array(122, 107),
	array(122, 121),
	array(122, 123),
	array(123, 108),
	array(123, 122),
	array(123, 124),
	array(124, 123),
	array(124, 109),
	array(124, 125),
	array(125, 110),
	array(125, 124),
	array(125, 126),
	array(126, 111),
	array(126, 125),
	array(126, 127),
	array(127, 112),
	array(127, 126),
	array(127, 128),
	array(128, 127),
	array(128, 113),
	array(128, 129),
	array(129, 128),
	array(129, 130),
	array(130, 129),
	array(130, 131),
	array(131, 116),
	array(131, 130),
	array(131, 132),
	array(132, 117),
	array(132, 131),
	array(132, 133),
	array(133, 118),
	array(133, 132) 
);
global $connectionsTMP;
$connectionsTMP = $connectionsTMP2;
}
?>
