<?php

include 'sesh.php';
$artistID = $_SESSION[ 'artist' ];
$_SESSION[ 'artist' ] = $artistID;
require_once 'rockdb.php';
require_once 'navbar_rock.php';
require_once 'stylesAndScripts.php';

$connekt = new mysqli( $GLOBALS[ 'host' ], $GLOBALS[ 'un' ], $GLOBALS[ 'magicword' ], $GLOBALS[ 'db' ] );

if ( !$connekt ) {
	echo 'Darn. Did not connect.';
};

$gatherTrackInfo = "SELECT t.trackID, t.trackName, a.albumName, a.artistID, p1.pop, p1.date
						FROM tracks t
						INNER JOIN albums a ON a.albumID = t.albumID
						JOIN (SELECT p.* FROM popTracks p
								INNER JOIN (SELECT trackID, pop, max(date) AS MaxDate
											FROM popTracks  
											GROUP BY trackID) groupedp
								ON p.trackID = groupedp.trackID
								AND p.date = groupedp.MaxDate) p1 
						ON t.trackID = p1.trackID
						WHERE a.artistID = '$artistID'
						ORDER BY t.trackName ASC";

$getit = $connekt->query( $gatherTrackInfo );

if ( !$getit ) {
	echo 'Cursed-Crap. Did not run the query.';
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<title>Latest Fetched Tracks Stats</title>
	<?php echo $stylesAndSuch; ?>
</head>

<body>

	<div class="container">

		<?php echo $navbar ?>

		<!-- main -->

		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">Latest Tracks Info from My Database</h3>
			</div>
			<div class="panel-body">

				<?php if(!empty($getit)) { ?>
				
				<table class="table" id="tableotracks">
					<thead>
						<tr>
							<th onClick="sortColumn('albumName', 'ASC')"><div class="pointyHead">Album Name</div></th>
							<th onClick="sortColumn('trackName', 'DESC')"><div class="pointyHead">Track</div></th>
							<th onClick="sortColumn('pop', 'ASC')"><div class="pointyHead">Track Popularity</div></th>
							
							<!--
								<th>Date</th>
							--> 
						</tr>
					</thead>
					
					<tbody>
					<?php
						while ( $row = mysqli_fetch_array( $getit ) ) {
							$albumName = $row[ "albumName" ];
							$trackName = $row[ "trackName" ];
							$trackPop = $row[ "pop" ];
							$popDate = $row[ "date" ];
					?>
							<tr>
								<td><?php echo $albumName ?></td>
								<td><?php echo $trackName ?></td>
								<td><?php echo $trackPop ?></td>
								
								<!--
								<td><?php //echo $popDate ?></td>
								-->
							</tr>
					<?php 
						} // end of while
					?>
					
					</tbody>
				</table>
				<?php 
					} // end of if
				?>
			</div> <!-- panel body -->
		</div> <!-- panel panel-primary -->
	</div> <!-- closing container -->
	
<?php echo $scriptsAndSuch; ?>
<script src="https://www.roxorsoxor.com/poprock/sortTheseTracks.js"></script>
</body>
	
</html>