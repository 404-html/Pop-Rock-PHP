<?php

require ("class.artist.php");
require_once 'rockdb.php';

$hollywoodVampires = new artist ("3k4YA0uPsWc2PuOQlJNpdH","Hollywood Vampires");
$aliceCooper = new artist("3EhbVgyfGd7HkpsagwL9GS","Alice Cooper");
$davidBowie = new artist("0oSGxfWSnnOXhD2fKuz2Gy","David Bowie");
$bobDylan = new artist("74ASZWbe4lXaubB36ztrGX","Bob Dylan");
$meatLoaf = new artist("7dnB1wSxbYa8CejeVg98hz","Meat Loaf");
$johnMellencamp = new artist("3lPQ2Fk5JOwGWAF3ORFCqH","John Mellencamp");
$stooges = new artist("4BFMTELQyWJU1SwqcXMBm3","The Stooges");
$bruceSpringsteen = new artist("3eqjTLE0HfPfh78zjh6TqT","Bruce Springsteen");
$kiss = new artist("07XSN3sPlIlB2L2XNcTwJw","Kiss");
$iggyPop = new artist("33EUXrFKGjpUSGacqEHhU4","Iggy Pop");
$popWilliamson = new artist("1l8grPt6eiOS4YlzjIs0LF","Iggy Pop & James Williamson");
$rollingStones = new artist("22bE4uQ6baNwSHPVcDxLCe","Rolling Stones");
$tomPetty = new artist("2UZMlIwnkgAEDBsw1Rejkn","Tom Petty");
$tpHeartbreakers = new artist("4tX2TplrkIP4v05BNC903e","Tom Petty & the Heartbreakers");
$travelingWilburys = new artist("2hO4YtXUFJiUYS2uYFvHNK","Traveling Wilburys");

$artists = array ("3k4YA0uPsWc2PuOQlJNpdH","3EhbVgyfGd7HkpsagwL9GS","0oSGxfWSnnOXhD2fKuz2Gy","74ASZWbe4lXaubB36ztrGX","7dnB1wSxbYa8CejeVg98hz","3lPQ2Fk5JOwGWAF3ORFCqH","4BFMTELQyWJU1SwqcXMBm3","3eqjTLE0HfPfh78zjh6TqT","07XSN3sPlIlB2L2XNcTwJw","33EUXrFKGjpUSGacqEHhU4","1l8grPt6eiOS4YlzjIs0LF","22bE4uQ6baNwSHPVcDxLCe","2UZMlIwnkgAEDBsw1Rejkn","4tX2TplrkIP4v05BNC903e");

$artists2 = array ("2hO4YtXUFJiUYS2uYFvHNK");


function getArtistsPop ($artists) {
				
	$artistsIds = implode(',', $artists);

	$bunchofartists = $GLOBALS['api']->getArtists($artistsIds);
		
	foreach ($bunchofartists->artists as $artist) {

		$artistID = $artist->id;
		$artistName = $artist->name;
		$artistPop = $artist->popularity;
		
		$insertArtistsPop = "INSERT INTO popArtists (artistID,pop) VALUES('$artistID','$artistPop')";
		
		$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);
		
		if (!$connekt) {
			echo 'Darn. Did not connect.';
		};
		
		$rockout = $connekt->query($insertArtistsPop);
		
		if(!$rockout){
			echo 'Cursed-Crap. Could not insert artists popularity.';
		}
	
		echo '<tr><td>' . $artistName . '</td><td>' . $artistPop . '</td></tr>';

		// When attempt is complete, connection closes
		mysqli_close($connekt);

	}
	
}

function getArtistsAndPop ($artists) {
	
	$artistsIds = implode(',', $artists);

	$bunchofartists = $GLOBALS['api']->getArtists($artistsIds);

	$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);

	foreach ($bunchofartists->artists as $artist) {

		$artistID = $artist->id;
		$artistName = $artist->name;
		$artistPop = $artist->popularity;

		$insertArtistsPop = "INSERT INTO popArtists (artistID,pop) VALUES('$artistID','$artistPop')";

		if (!$connekt) {
			echo 'Darn. Did not connect.';
		};

		$rockout = $connekt->query($insertArtistsPop);

		if(!$rockout){
			echo 'Cursed-Crap. Could not insert artists popularity.';
		}

		echo '<tr><td>' . $artistName . '</td><td>' . $artistPop . '</td></tr>';

	}

	// When attempt is complete, connection closes
	mysqli_close($connekt);

}

function showArtists () {
	
	$artistInfo = "SELECT a.artistID, a.artistName, b.pop 
		FROM artists a
			INNER JOIN popArtists b ON a.artistID = b.artistID
				ORDER BY a.artistName ASC";

	// how do I get most recent pop?

	$getit = $connekt->query($artistInfo);

	while ($row = mysqli_fetch_array($getit)) {
		// $artistID = $row["artistID"];
		$artistName = $row["artistName"];
		$artistPop = $row["pop"];
		
		echo "<tr>";
		echo "<td>" . $artistName . "</td>";
		echo "<td>" . $artistPop . "</td>";
		echo "</tr>";
	}
}

?>