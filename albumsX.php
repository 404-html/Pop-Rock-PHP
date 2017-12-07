<?php

$artistAlbums = array ();
require_once 'rockdb.php';

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

	$howmany = count($albumsArrays);
	echo $howmany . '<br>';

	for ($i=0; $i<(count($albumsArrays)); ++$i) {

		$howmanyhere = count($albumsArrays[$i]);
		echo $howmanyhere . '<br>';
				
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

			$insertAlbums = "INSERT INTO albums (albumID,albumName,artistID,year) VALUES('$albumID','$albumName','$thisArtistID','$albumReleased')";
			
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
		
            echo '<tr><td>' . $thisArtistName . '</td><td>' . $albumName . '</td><td>' . $albumReleased . '</td><<td>' . $albumPop . '</td></tr>';

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

function showAlbums ($artistID) {

	$connekt = new mysqli($GLOBALS['host'], $GLOBALS['un'], $GLOBALS['magicword'], $GLOBALS['db']);

	$query = "SELECT a.albumID, a.albumName, a.artistID, a.year, b.pop, c.artistName 
		FROM albums a
			INNER JOIN popAlbums b ON a.albumID = b.albumID
			INNER JOIN artists c ON a.artistID = c.artistID
				WHERE a.artistID = '$artistID'
					ORDER BY a.year ASC;";

	$query2 = "SELECT a.albumID, a.albumName, a.artistID, a.year, b.pop, b.date, c.artistName
		FROM albums a 
			INNER JOIN popAlbums b ON a.albumID = b.albumID
				WHERE b.date = (SELECT max(b2.date)
								FROM popAlbums b2)
			INNER JOIN artists c ON a.artistID = c.artistID
				WHERE a.artistID = '$artistID'
					ORDER BY a.year ASC;";
					
	$queryInProgress = "SELECT a.albumID, a.albumName, a.artistID, a.year, b.pop, b.date, c.artistName
		FROM albums a 
			WHERE a.artistID = '$artistID'
		JOIN popAlbums b ON a.albumID = b.albumID
				WHERE b.date = (SELECT max(b2.date)
								FROM popAlbums b2)
			INNER JOIN artists c ON a.artistID = c.artistID
					ORDER BY a.year ASC;";	
					
	$query3 = "SELECT a.albumID, a.albumName, a.artistID, a.year, b.pop, b.date
		FROM albums a 
			WHERE a.artistID = '$artistID'
		JOIN popAlbums b ON a.albumID = b.albumID
				WHERE b.date = (SELECT max(b2.date)
								FROM popAlbums b2);";	
								
	$query4a = "SELECT p.albumID, a.albumName, a.artistID, a.year, p.pop, p.date, (SELECT artistName FROM artists WHERE artistID = '$artistID')
		FROM popAlbums p
			WHERE p.date = (SELECT max(p2.date)
							FROM popAlbums p2)
		RIGHT JOIN albums a 
		ON p.albumID = a.albumID
			WHERE a.artistID = '$artistID';";
			
	$query4b = "SELECT p.albumID, a.albumName, a.artistID, a.year, p.pop, p.date, (SELECT artistName FROM artists WHERE artistName = '3EhbVgyfGd7HkpsagwL9GS')
		FROM popAlbums p
			WHERE p.date = (SELECT max(p2.date)
							FROM popAlbums p2)
		RIGHT JOIN albums a 
		ON p.albumID = a.albumID
			WHERE a.artistID = '$3EhbVgyfGd7HkpsagwL9GS';";			
			
	$query5a = "SELECT a.albumID, a.albumName, a.artistID, a.year, q.pop, q.date, c.artistName
		FROM albums a 
			WHERE a.artistID = '$artistID'
		JOIN (SELECT z.albumID, z.pop, z.date 
				FROM popAlbums z
				WHERE z.date = (SELECT max(z2.date)
							FROM popAlbums z2)) q
			ON q.albumID = a.albumID
		JOIN artists c ON c.artistID = '$artistID'
		ORDER BY a.year ASC;";	
		SELECT a.albumID, a.albumName, a.artistID, a.year, q.pop, q.date, c.artistName
		FROM albums a 
			WHERE a.artistID = '3EhbVgyfGd7HkpsagwL9GS'
		JOIN (SELECT z.albumID, z.pop, z.date 
				FROM popAlbums z
				WHERE z.date = (SELECT max(z2.date)
							FROM popAlbums z2)) q
			ON q.albumID = a.albumID
		JOIN artists c ON c.artistID = '3EhbVgyfGd7HkpsagwL9GS'
		ORDER BY a.year ASC;
	$query5b = "SELECT a.albumID, a.albumName, a.artistID, a.year, q.pop, q.date, c.artistName
		FROM albums a 
			WHERE a.artistID = '3EhbVgyfGd7HkpsagwL9GS'
		JOIN (SELECT z.albumID, z.pop, z.date 
				FROM popAlbums z
				WHERE z.date = (SELECT max(z2.date)
							FROM popAlbums z2)) q
			ON q.albumID = a.albumID
		JOIN artists c ON c.artistID = '3EhbVgyfGd7HkpsagwL9GS'
		ORDER BY a.year ASC;";		
					
	$maybe = "SELECT  R.SId ,R.SName,R.Sprice
				FROM (SELECT  Staff.SId ,Staff.SName,Sprice,updateStaff.SDate,updateStaff.stime
					  FROM Staff
						LEFT JOIN updateStaff ON Staff.SId = updateStaff.SId ) AS R
				WHERE R.stime = (SELECT MAX(stime) FROM updateStaff us WHERE us.SId =R.SId
					  and us.sdate =(select max(sdate) from updateStaff us2 where us2.sid = us.sid))
				ORDER BY R.SId , R.SName;";
				
	$maybe2 = "SELECT	b.ID,
						b.BookingID,
						a.Name,
						b.departureDate,
						b.Amount
				FROM    Table1 a
						INNER JOIN Table2 b
							ON a.ID = b.BookingID
						INNER JOIN
						(
							SELECT  BookingID, MAX(DepartureDate) Max_Date
							FROM    Table2
							GROUP   BY BookingID
						) c ON  b.BookingID = c.BookingID AND
								b.DepartureDate = c.Max_date";

// the next line works in stakeout but not here
	// $result = $connekt->query($query);

	$result = mysqli_query($connekt,$query2);

	while ($row = mysqli_fetch_array($result)) {
		// $artistID = $row["artistID"];
		$artistName = $row['artistName'];
		$albumName = $row['albumName'];
		$albumReleased = $row['year'];
		$albumPop = $row['pop'];
		
		echo "<tr>";
		echo "<td>" . $artistName . "</td>";
		echo "<td>" . $albumName . "</td>";
		echo "<td>" . $albumReleased . "</td>";
		echo "<td>" . $albumPop . "</td>";
		echo "</tr>";
	}
}

?>