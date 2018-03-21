<?php

$artistAlbums = array ();
require_once 'rockdb.php';

function showAlbums ($artistID) {

	$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);

	if (!$connekt) {
		echo 'Darn. Did not connect.';
	};

	$happyScabies2 = "SELECT a.albumName, a.year, a.albumArt, z.artistName, p1.pop, p1.date
						FROM (SELECT
									y.albumID AS albumID,
									y.albumName AS albumName,
									y.artistID AS artistID,
									y.albumArt AS albumArt,
									y.year AS year
								FROM albums y 
								WHERE y.artistID = '$artistID') a
						JOIN artists z ON z.artistID = '$artistID'
						JOIN (SELECT p.*
								FROM popAlbums p
								INNER JOIN (SELECT albumID, pop, max(date) AS MaxDate
											FROM popAlbums  
											GROUP BY albumID) groupedp
								ON p.albumID = groupedp.albumID
								AND p.date = groupedp.MaxDate) p1 
						ON a.albumID = p1.albumID
						ORDER BY year ASC;";
// ORDER BY a.year ASC;"; 
	$getit = $connekt->query($happyScabies2);

	if(!$getit){
		echo 'Cursed-Crap. Did not run the query.';
	}

	echo '<table class="table" id="recordCollection">
			<tr><thead>
				<th>Album Art</th>
				<th onClick="sortColumn(\'<?php echo $albumName ?>\', \'ASC\')">Album Name</th>
				<th onClick="sortColumn(\'<?php echo $albumReleased ?>\', \'ASC\')">Released</th>
				<th onClick="sortColumn(\'<?php echo $albumPop ?>\', \'ASC\')">Popularity</th>
				<th>Date</th>
			</thead></tr>
			<tbody>';

	while ($row = mysqli_fetch_array($getit)) {
		// $artistID = $row["artistID"];
		$artistName = $row['artistName'];
		$albumArt = $row['albumArt'];
		$albumName = $row['albumName'];
		$albumReleased = $row['year'];
		$albumPop = $row['pop'];
		$date = $row['date'];

		// $rows[] = $row;
		
		echo "<tr>";
		echo "<td><img src='" . $albumArt . "' height='64' width='64'></td>";
		echo "<td>" . $albumName . "</td>";
		echo "<td>" . $albumReleased . "</td>";
		echo "<td>" . $albumPop . "</td>";
		echo "<td>" . $date . "</td>";
		echo "</tr>";

	}

	echo "</tbody></table>";

	// echo json_encode($rows);
	// make the above echo a js script that sends the json to the console

}

function divideCombineAlbumsForArt ($artistAlbums) {
	
	// Divide all artist's albums into chunks of 20
	$artistAlbumsChunk = array ();
	$x = ceil((count($artistAlbums))/20);

	$firstAlbum = 0;
	
    for ($i=0; $i<$x; ++$i) {
	  $lastAlbum = 19;
	  $artistAlbumsChunk = array_slice($artistAlbums, $firstAlbum, $lastAlbum);
      $albumsArrays [] = $artistAlbumsChunk;
      $firstAlbum += 20;
	};

	for ($i=0; $i<(count($albumsArrays)); ++$i) {

		$howmanyhere = count($albumsArrays[$i]);
				
		$albumIds = implode(',', $albumsArrays[$i]);
	
		// For each array of albums (20 at a time), "get several albums"
		$bunchofalbums = $GLOBALS['api']->getAlbums($albumIds);
			
		foreach ($bunchofalbums->albums as $album) {

			$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);
	
			$albumID = $album->id;	
			$albumArt = $album->images[0]->url;
			$albumNameYucky = $album->name;
			$albumName = mysqli_real_escape_string($connekt,$albumNameYucky);
			$albumReleasedWhole = $album->release_date;
			$albumReleased = substr($albumReleasedWhole, 0, 4);
			$thisArtistID = $album->artists[0]->id;
			$thisArtistName = $album->artists[0]->name;
			$albumPop = $album->popularity;

			$insertAlbumArt = "UPDATE albums SET albumArt = '$albumArt' WHERE albumID = '$albumID'";
			
			if (!$connekt) {
				echo 'Darn. Did not connect.<br>';
			};
			
			$rockout = $connekt->query($insertAlbumArt);

			if(!$rockout){
				echo 'Crapola! Could not add album art.<br>';
			}

			$insertAlbumsPop = "INSERT INTO popAlbums (albumID,pop) VALUES('$albumID','$albumPop')";
			
			$rockin = $connekt->query($insertAlbumsPop);
			
			if(!$rockin){
				echo 'Sweet Christmas! Could not insert albums popularity.';
			}
		
            echo '<tr><td><img src="' . $albumArt . '" height="64" width="64"></td><td>' . $albumName . '</td><td>' . $albumReleased . '</td><<td>' . $albumPop . '</td></tr>';

		}
	};
}

function divideCombineAlbumsForTracks ($artistAlbums) {

	$albumsArrays = array ();
	
	// Divide all artist's albums into chunks of 20
	$artistAlbumsChunk = array ();
	$x = ceil((count($artistAlbums))/20);

	$firstAlbum = 0;
	
    for ($i=0; $i<$x; ++$i) {
	  $lastAlbum = 19;
      $artistAlbumsChunk = array_slice($artistAlbums, $firstAlbum, $lastAlbum);
	  // put chunks of 20 into an array
      $albumsArrays [] = $artistAlbumsChunk;
      $firstAlbum += 20;
	};

	for ($i=0; $i<(count($albumsArrays)); ++$i) {
				
		$albumIds = implode(',', $albumsArrays[$i]);
	
		// For each array of albums (20 at a time), "get several albums"
		$bunchofalbums = $GLOBALS['api']->getAlbums($albumIds);
			
		foreach ($bunchofalbums->albums as $album) {

			$AlbumsTracks = array();
	
			$albumID = $album->id;
			
			$thisAlbumTracks = $GLOBALS['api']->getAlbumTracks($albumID);

			// should be method in albums class
			foreach ($thisAlbumTracks->items as $track) {
				
				// Get each trackID for requesting Full Track Object with popularity
				$trackID = $track->id;
				
				// Put trackIDs in array for requesting several at a time (far fewer requests)
				$AlbumsTracks [] = $trackID;
				
			}

			divideCombineInsertTracksAndPop ($AlbumsTracks);
			// divideCombineInsertPopTracks ($AlbumsTracks);
			unset($AlbumsTracks);
			
		}
	};
}

function divideCombineAlbums ($artistAlbums) {
	
	// Divide all artist's albums into chunks of 20
	$artistAlbumsChunk = array ();
	$x = ceil((count($artistAlbums))/20);

	$firstAlbum = 0;
	
    for ($i=0; $i<$x; ++$i) {
	  $lastAlbum = 19;
	  $artistAlbumsChunk = array_slice($artistAlbums, $firstAlbum, $lastAlbum);
	  // $howmanytotal = count($artistAlbumsChunk);
	  // echo $howmanytotal . '<br>';
	  // put chunks of 20 into an array
      $albumsArrays [] = $artistAlbumsChunk;
      $firstAlbum += 20;
	};

	// $howmany = count($albumsArrays);
	// echo $howmany . '<br>';

	for ($i=0; $i<(count($albumsArrays)); ++$i) {

		// $howmanyhere = count($albumsArrays[$i]);
		// echo $howmanyhere . '<br>';
				
		$albumIds = implode(',', $albumsArrays[$i]);
	
		// For each array of albums (20 at a time), "get several albums"
		$bunchofalbums = $GLOBALS['api']->getAlbums($albumIds);
			
		foreach ($bunchofalbums->albums as $album) {

			$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);
	
			$albumID = $album->id;	
			$albumNameYucky = $album->name;
			$albumName = mysqli_real_escape_string($connekt,$albumNameYucky);
			// $albumName = mysqli_real_escape_string($connekt, htmlspecialchars($albumNameYucky));
			$albumReleasedWhole = $album->release_date;
			$albumReleased = substr($albumReleasedWhole, 0, 4);
			$thisArtistID = $album->artists[0]->id;
			$thisArtistName = $album->artists[0]->name;
			$albumPop = $album->popularity;
			$albumArt = $album->images[0]->url;

			$insertAlbums = "INSERT INTO albums (albumID,albumName,artistID,year,albumArt) VALUES('$albumID','$albumName','$thisArtistID','$albumReleased','$albumArt')";
			
			if (!$connekt) {
				echo 'Darn. Did not connect.<br>';
			};
			
			$rockout = $connekt->query($insertAlbums);

			if(!$rockout){
				echo 'Crap de General Tsao! Could not insert album.<br>';
			}

			$insertAlbumsPop = "INSERT INTO popAlbums (albumID,pop) VALUES('$albumID','$albumPop')";

			$rockin = $connekt->query($insertAlbumsPop);
			
			if(!$rockin){
				echo 'Sweet & Sour Crap! Could not insert albums popularity.';
			}
		
            echo '<p><img src="' . $albumArt . '" height="64" width="64"><br>' . $albumName . '<br>' . $albumReleased . '<br>Pop is ' . $albumPop . '</p>';

		}
	};
  
}

function getAlbumsPop ($artistAlbums) {
	
	// Divide all artist's albums into chunks of 20
	$artistAlbumsChunk = array ();
	$x = ceil((count($artistAlbums))/20);

	$firstAlbum = 0;
	
    for ($i=0; $i<$x; ++$i) {
	  $lastAlbum = 19;
      $artistAlbumsChunk = array_slice($artistAlbums, $firstAlbum, $lastAlbum);
	  // put chunks of 20 into an array
      $albumsArrays [] = $artistAlbumsChunk;
      $firstAlbum += 20;
	};

	for ($i=0; $i<(count($albumsArrays)); ++$i) {
				
		$albumIds = implode(',', $albumsArrays[$i]);
	
		// For each array of albums (20 at a time), "get several albums"
		$bunchofalbums = $GLOBALS['api']->getAlbums($albumIds);
			
		foreach ($bunchofalbums->albums as $album) {

			$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);
	
			$albumID = $album->id;
			$artistName = $album->artists[0]->name;
			$albumPop = $album->popularity;
			$albumNameYucky = $album->name;
			$albumName = mysqli_real_escape_string($connekt,$albumNameYucky);
			$albumReleasedWhole = $album->release_date;
			$albumReleased = substr($albumReleasedWhole, 0, 4);
			$insertAlbumsPop = "INSERT INTO popAlbums (albumID,pop) VALUES('$albumID','$albumPop')";
			
			if (!$connekt) {
				echo 'Darn. Did not connect.';
			};
			
			$rockout = $connekt->query($insertAlbumsPop);

			if(!$rockout){
				echo 'Sweet & Sour Crap! Could not insert albums popularity.';
			}
						
			echo "<tr>";
			echo "<td>" . $artistName . "</td>";
			echo "<td>" . $albumName . "</td>";
			echo "<td>" . $albumReleased . "</td>";
			echo "<td>" . $albumPop . "</td>";
			echo "</tr>";

		}
	};
  
}

?>