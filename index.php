<?php

require_once('./config.inc.php');
require_once('./auth.inc.php');

$dateFrom = isset($_GET['dateFrom']) ? $_GET['dateFrom'] : null;
$dateTo = isset($_GET['dateTo']) ? $_GET['dateTo'] : null;

if(isset($_GET['accuracy']) && $_GET['accuracy'] != '' && intval($_GET['accuracy']) > 0){
	$accuracy = intval($_GET['accuracy']);
}else if(isset($_COOKIE['accuracy']) && $_COOKIE['accuracy'] != '' && intval($_COOKIE['accuracy']) > 0){
	$accuracy = intval($_COOKIE['accuracy']);
}else{
	$accuracy = $_config['default_accuracy'];
}

if(isset($_GET['trackerID']) && $_GET['trackerID'] != '' && strlen($_GET['trackerID']) == 2){
	$trackerID = $_GET['trackerID'];
}else if(isset($_COOKIE['trackerID']) && $_COOKIE['trackerID'] != '' && strlen($_COOKIE['trackerID']) == 2){
	$trackerID = $_COOKIE['trackerID'];
}else{
	$trackerID = $_config['default_trackerID'];
}

?>
<html>
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
		<title>Your Own Tracks</title>
		<link rel="icon" href="./img/favicon.ico" />

		<!-- JQUERY !-->
		<script src="//code.jquery.com/jquery-3.3.1.min.js"></script>

		<!-- MOMENTS.JS !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script>

		<!-- HIGHCHARTS.JS !-->
		<script src="//code.highcharts.com/highcharts.src.js"></script>

		<!-- BOOTSTRAP !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/js/bootstrap.min.js" ></script>

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/js/bootstrap-datepicker.min.js"></script>
		<?php
			if (is_array($_config['locale']) && !empty($_config['locale']['datepicker'])) {
				echo '<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/locales/bootstrap-datepicker.' . $_config['locale']['datepicker'] . '.min.js"></script>';
			}
		?>

		<!-- LEAFLET.JS !-->
		<script src="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/leaflet.js"></script>
		<script src="//cdn.jsdelivr.net/npm/leaflet-hotline@0.4.0/src/leaflet.hotline.min.js"></script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.min.js"></script>

		<script src="//cdnjs.cloudflare.com/ajax/libs/js-cookie/2.2.0/js.cookie.min.js"></script>

		<!-- cleanup final DOM to remove any top-level elements injected by a free webhost !-->
		<script>
			jQuery(document).ready(function($){
				$('body > *:not(div.container)').remove();
			})
		</script>

		<!-- BOOTSTRAP !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
		<link rel="stylesheet" href="//getbootstrap.com/docs/3.3/dist/css/bootstrap.min.css" />

		<!-- BOOTSTRAP DATETIMEPICKER !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.8.0/css/bootstrap-datepicker.css" />

		<!-- LEAFLET.JS !-->
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/leaflet/1.3.4/leaflet.css" />
		<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/Leaflet.awesome-markers/2.0.2/leaflet.awesome-markers.css" />

		<style>
			#mapid { height: 85%; }

			#mapid a[role="button"] { color: blue; }

			#mapid table { border-collapse: collapse; }
			#mapid table td { padding: 5px 10; border: 1px solid #ccc; }

			.disabled {
				pointer-events: none;
				cursor: default;
				opacity: 0.5;
			}

			/*
			 * tested in:
			 *   - Chrome 90 desktop   (with    support for CSS3, HTML5, ES6; by manually resizing window)
			 *   - Chrome 30 desktop   (without support for CSS3, HTML5, ES6; by manually resizing window)
			 *   - Android 4.4 WebView (without support for CSS3, HTML5, ES6; small screen device)
			 */
			@media screen {
				.container, .row, .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9 { margin: 0px !important; padding: 0px !important; }
				.datepicker.dropdown-menu { z-index: 999 !important; }

				form { margin-bottom: 0; }

				.btn,
				.page-header.row input[type="text"],
				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select,
				.input-group-addon { height: 34px; padding: 6px 12px; }
				.input-group-addon { line-height: 20px; }

				.page-header.row { padding: 15px 5px !important; margin-bottom: 15px !important; }
				.page-header.row * { display: inline-block !important; width: auto !important; float: none !important; }
				.page-header.row > div.text-right { margin-left: auto !important; }
				.page-header.row > div > * { width: 100%; }
				.page-header.row .glyphicon { min-width: 15px; }
				.page-header.row input[type="text"] { font-size: 0.75em; max-width: 8em; text-overflow: clip; }

				#configCollapse > div.well { padding: 15px 5px; }
				#configCollapse > div.well * { display: inline-block !important; width: auto !important; float: none !important; }
				#configCollapse > div.well > div:last-child { float: right !important; }
				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select { font-size: 1em; max-width: 7em; }
			}
			@media screen and (min-width: 767px) {
				.page-header.row .visible-xs,
				#configCollapse > div.well .visible-xs { display: none !important; }
			}
			@media screen and (max-width: 767px) {
				.page-header.row .hidden-xs,
				#configCollapse > div.well .hidden-xs { display: none !important; }
			}
			@media screen and (max-width: 460px) {
				.btn,
				.page-header.row input[type="text"],
				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select,
				.input-group-addon { padding: 6px; }
			}
			@media screen and (max-width: 400px) {
				.btn,
				.page-header.row input[type="text"],
				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select,
				.input-group-addon { padding: 6px 3px; }

				.page-header.row input[type="text"] { max-width: 6em; }

				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select { font-size: 0.75em; }
			}
			@media screen and (max-width: 310px) {
				/* When screen is less than 310px, date input fields will truncate. By changing direction from right-to-left, truncation will occur on the left side.. which is preferable since the year is mostly static. However, this setting makes manual input much less intuitive.. which is why this setting isn't applied for all screen sizes. */
				.page-header.row input[type="text"] { font-size: 0.6em; max-width: 5em; direction: rtl; }
			}
			@media screen and (max-width: 285px) {
				#configCollapse > div.well input[type="number"],
				#configCollapse > div.well select { font-size: 0.65em; max-width: 5em; }
			}
			@media screen and (max-width: 265px) {
				.page-header.row input[type="text"] { font-size: 0.5em; }
			}
			@media screen and (max-width: 255px) {
				#configCollapse > div.well > div:last-child .input-group-addon { display: none !important; }
			}
			@media screen and (max-width: 250px) {
				.page-header.row input[type="text"] { max-width: 4em; }
			}
			@media screen and (max-width: 235px) {
			}
		</style>
	</head>
	<body>
		<div class="container">
			<div class="row page-header">
				<div class="col-xs-1 text-left">
					<a onclick="gotoDate(datePrevFrom, datePrevTo);" class="btn btn-primary" role="button" title="Previous">
						<span class="hidden-xs">Previous</span>
						<span class="visible-xs"><span class="glyphicon glyphicon-arrow-left"></span></span>
					</a>
				</div>
				<div class="col-xs-5 text-center">
					<div class="input-group input-daterange ">
							<input type="text" class="form-control" value="" id="dateFrom">
							<span class="input-group-addon">to</span>
							<input type="text" class="form-control" value="" id="dateTo">
					</div>
				</div>
				<div class="col-xs-6 text-right">
					<div class="btn-group" role="group">
						<a role="button" data-toggle="collapse" href="#configCollapse" class="btn btn-default" id="configButton" title="Config">
							<span class="hidden-xs">Config</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-cog"></span></span>
						</a>

						<a role="button" onclick="resetZoom();" class="btn btn-default" title="Reset view">
							<span class="hidden-xs">Reset view</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-screenshot"></span></span>
						</a>
						<a role="button" onclick="gotoDate();" class="btn btn-default" style="display: inline-block;" id="todayButton" title="Today">
							<span class="hidden-xs">Today</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-arrow-up"></span></span>
						</a>
						<a role="button" onclick="gotoDate(dateNextFrom, dateNextTo);" class="btn btn-primary" style="display: inline-block;" id="nextButton" title="Next">
							<span class="hidden-xs">Next</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-arrow-right"></span></span>
						</a>
					</div>
				</div>
			</div>
			<div class="collapse" id="configCollapse">
				<div class="well">
					<div class="row">
						<div class="col-xs-2 text-left">
							<a role="button" onclick="showHideMarkers();" class="btn btn-default" id="show_markers" title="Show markers">
							<span class="hidden-xs">Show markers</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-map-marker"></span></span>
						</a>
					</div>
					<div class="col-xs-2 text-left">
						<a role="button" onclick="setLiveMap();" class="btn btn-default" id="livemap_on" title="Live map">
							<span class="hidden-xs">Live map</span>
							<span class="visible-xs"><span class="glyphicon glyphicon-play-circle"></span></span>
						</a>
					</div>
					<div class="col-xs-8 text-right">
						<form class="form-inline"><span class="hidden-xs">Accuracy : </span>
								<div class="input-group">
									<input type="number" size='4' class="form-control" id="accuracy" value="<?php echo $accuracy; ?>" />
								<span class="input-group-addon"><span class="hidden-xs">meters</span><span class="visible-xs">m</span></span>
								<span class="input-group-btn"><button type="button" class="btn btn-default" id="accuracySubmit">OK</button></span>
							</div>
						</form>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 text-left">
						<div class="input-group">
							<div class="input-group-addon">
								<span class="hidden-xs">Tracker ID</span>
								<span class="visible-xs"><span class="glyphicon glyphicon-user"></span></span>
							</div>
							<select class="form-control" name="tracker_id" id="trackerID_selector" style="">
									<option value="all"><?php echo $_config['default_trackerID']; ?></option>
							</select>
						</div>
					</div>
				</div>
				</div>
			</div>
			<script>
				// compensate for up to a 12 hour timezone difference between (location of) client and (configuration of) server
				var dateTodayNoon = moment().startOf('day').add(12, 'hours');

				//app parameters vars
				var dateFrom;
				var dateTo;
				var accuracy;
				var datePrevFrom;
				var datePrevTo;
				var dateNextFrom;
				var dateNextTo;
				var trackerID;
				var trackerIDs = [];

				// map management vars
				var i;
				var map_drawn = false;
				var show_markers;
				var mymap;
				var tid_markers; // markers collected from json
				var nb_markers = 0;
				var my_markers = [];
				var my_latlngs = [];
				var polylines = [];
				var default_zoom;
				var default_center;
				var live_view_timer = 0;

				var marker_start_icons = [];
				var marker_finish_icons = [];
				var marker_icons = [];

///// INIT
				$( document ).ready(function() {
					initApp();
				});

				function initApp(){
					initUI();
					initMap();
					initCharts();
				}

				function initUI(){
					console.log("initUI : INIT");

					dateFrom = <?php echo empty($dateFrom) ? 'null' : 'moment("' . $dateFrom . '")' ?>;
					dateTo   = <?php echo empty($dateTo)   ? 'null' : 'moment("' . $dateTo   . '")' ?>;

					if (!dateFrom || !dateFrom.isValid()) dateFrom = dateTodayNoon;
					if (!dateTo   || !dateTo.isValid())   dateTo   = dateTodayNoon;

					$('#dateFrom').val(dateFrom.format('YYYY-MM-DD'));
					$('#dateTo').val(dateTo.format('YYYY-MM-DD'));

					$('.input-daterange').datepicker({
						format: 'yyyy-mm-dd',
						<?php
							if (is_array($_config['locale']) && !empty($_config['locale']['datepicker'])) {
								echo 'language: "' . $_config['locale']['datepicker'] . '",';
							}
						?>
						endDate: '0d',
					});

					$('.input-daterange').datepicker().on('hide', function(e) {
						return gotoDate($('#dateFrom').val(), $('#dateTo').val());
					});

					//accuracy event handlers
					accuracy = <?php echo $accuracy; ?>;
					$('#accuracy').change(function(){
						gotoAccuracy();
					});
					$('#accuracySubmit').click(function(){
						gotoAccuracy();
					});

					//trackerID event handlers
					trackerID = "<?php echo $trackerID; ?>";

					$('#trackerID_selector').change(function(){
						gotoTrackerID();
					});

					$('#configCollapse').on('show.bs.collapse', function (e) {
						$('#configButton').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
					})
					$('#configCollapse').on('hide.bs.collapse', function (e) {
						$('#configButton').addClass( "btn-default" ).removeClass( "btn-primary" ).removeClass( "active" );
					})

					//setup history popupstate event handler
					window.onpopstate = handlePopState;
				}

				/**
				* initiate map config and fire getMarkers function
				* fired once on document.ready
				*/
				function initMap(){
					console.log("initMap : INIT");

					show_markers = Cookies.get('show_markers');
					console.log("initMap : INFO show_markers = " + show_markers);

					marker_start_icons[0] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'blue', iconColor: 'green' });
					marker_start_icons[1] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'red', iconColor: 'green' });
					marker_start_icons[2] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'orange', iconColor: 'green' });
					marker_start_icons[3] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'green', iconColor: 'darkgreen' });
					marker_start_icons[4] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'purple', iconColor: 'green' });
					marker_start_icons[5] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'cadetblue', iconColor: 'green' });
					marker_start_icons[6] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkred', iconColor: 'green' });
					marker_start_icons[7] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkgreen', iconColor: 'green' });
					marker_start_icons[8] = L.AwesomeMarkers.icon({icon: 'play', markerColor: 'darkpuple', iconColor: 'green' });

					marker_finish_icons[0] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'blue', iconColor: 'red' });
					marker_finish_icons[1] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'red', iconColor: 'darkred' });
					marker_finish_icons[2] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'orange', iconColor: 'red' });
					marker_finish_icons[3] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'green', iconColor: 'red' });
					marker_finish_icons[4] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'purple', iconColor: 'red' });
					marker_finish_icons[5] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'cadetblue', iconColor: 'red' });
					marker_finish_icons[6] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkred', iconColor: 'red' });
					marker_finish_icons[7] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkgreen', iconColor: 'red' });
					marker_finish_icons[8] = L.AwesomeMarkers.icon({icon: 'stop', markerColor: 'darkpuple', iconColor: 'red' });

					marker_icons[0] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'blue' });
					marker_icons[1] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'red' });
					marker_icons[2] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'orange' });
					marker_icons[3] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'green' });
					marker_icons[4] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'purple' });
					marker_icons[5] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'cadetblue' });
					marker_icons[6] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkred' });
					marker_icons[7] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkgreen' });
					marker_icons[8] = L.AwesomeMarkers.icon({icon: 'user', markerColor: 'darkpuple' });

					//set checkbox
					if(show_markers == '1'){
						//hideMarkers();
						//$('#show_markers').prop('checked',false);
						$('#show_markers').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
					}

					mymap = L.map('mapid').setView([48.866667, 2.333333], 11);

					L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
						attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
						subdomains: ['a','b','c']
					}).addTo( mymap );

					getMarkers();
				}

				function initCharts(){
				}

///// EVENT HANDLERS
				/**
				* handles navigation to another date range
				*/
				function gotoDate(_dateFrom, _dateTo, pushState){
					console.log("gotoDate : INIT");

					var _dateFrom = (typeof _dateFrom !== 'undefined') ? moment(_dateFrom) : null;
					var _dateTo   = (typeof _dateTo   !== 'undefined') ? moment(_dateTo)   : null;
					var pushState = (typeof pushState !== 'undefined') ? pushState : true;

					if (!_dateFrom || !_dateFrom.isValid()) _dateFrom = dateTodayNoon;
					if (!_dateTo   || !_dateTo.isValid())   _dateTo   = dateTodayNoon;

					dateFrom = _dateFrom;
					dateTo = _dateTo;

					$('#dateFrom').val(moment(dateFrom).format('YYYY-MM-DD'));
					$('#dateTo').val(moment(dateTo).format('YYYY-MM-DD'));

					//push selected dates in window.history stack
					if(pushState) { window.history.pushState(
						{dateFrom: moment(dateFrom).format('YYYY-MM-DD'), dateTo: moment(dateTo).format('YYYY-MM-DD')},
						'',
						window.location.pathname + '?password=<?php echo $_REQUEST['password']; ?>&dateFrom=' + moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD')
					)}

					getMarkers();
					return false;
				}

				/**
				* Adds two numbers
				* @return {Number} sum
				*/
				function gotoAccuracy(){
					console.log("gotoAccuracy : INIT");

					var _accuracy = parseInt($('#accuracy').val());

					if(_accuracy != accuracy){
						Cookies.set('accuracy', _accuracy);
						console.log("Accuracy cookie = " + Cookies.get('accuracy'));

						//location.href='./?password=<?php echo $_REQUEST['password']; ?>&dateFrom='+moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD') + '&accuracy=' + _accuracy + '&trackerID=' + trackerID;

						accuracy = _accuracy;
						getMarkers();
					}else{
						$('#configCollapse').collapse('hide');
					}
					return false;
				}

				/**
				* reset map on tracker ID change
				*/
				function gotoTrackerID(){
					console.log("gotoTrackerID : INIT");

					var _trackerID = $('#trackerID_selector').val();

					if(_trackerID != trackerID){
						Cookies.set('trackerID', _trackerID);
						console.log("gotoTrackerID : INFO trackerID cookie = " + Cookies.get('trackerID'));

						trackerID = _trackerID;
						drawMap();
					}else{
						$('#configCollapse').collapse('hide');
					}
					return false;
				}

				function handlePopState(event){
					console.log("handlePopState : INIT");
					console.log(event);

					return gotoDate(event.state.dateFrom, event.state.dateTo, false);
				}

				/**
				* Sets up the live update of new recorded markers
				*/
				function setLiveMap(){
					console.log("setLiveMap : INIT");

					if(live_view_timer){
						clearInterval(live_view_timer);
						live_view_timer = 0;
						$('#livemap_on').addClass( "btn-default" ).removeClass( "btn-primary" ).removeClass( "active" );
					}else{
						live_view_timer = setInterval(handleLiveMap, <?php echo $_config['live_map_interval']; ?>);
						$('#livemap_on').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
					}
				}

				function handleLiveMap(){
					getMarkers(/* is_live_map_update= */ true);
				}

///// UI HANDLERS
				/**
				* updates config bar UI based on user params
				*/
				function updateNavbarUI(_dateFrom, _dateTo){
					console.log("updateNavbarUI : INIT");

					if(typeof _dateFrom == "undefined") { _dateFrom = dateFrom; }
					if(typeof _dateTo == "undefined") { _dateTo = dateTo; }

					var now  = moment();
					var diff = _dateTo.diff(_dateFrom, 'days');

					datePrevTo   = moment(_dateFrom).subtract(1, 'days');
					datePrevFrom = moment(datePrevTo).subtract(diff, 'days');

					dateNextFrom = moment(_dateTo).add(1, 'days');
					dateNextTo   = moment(dateNextFrom).add(diff, 'days');

					//disable Next button
					if(dateNextFrom.isAfter(now, 'day')){
						$('#nextButton').addClass('disabled');
					}else{
						$('#nextButton').removeClass('disabled');
					}

					//disable today button
					if(_dateFrom.isSame(now, 'day') && _dateTo.isSame(now, 'day')){
						$('#todayButton').addClass('disabled');
						$('#livemap_on').removeClass('disabled');
					}else{
						if (live_view_timer) setLiveMap();

						$('#todayButton').removeClass('disabled');
						$('#livemap_on').addClass('disabled');
					}
				}

				/*
				* UI update
				* updates tracker ID selection dropdown list from config
				*
				*/

				function updateTrackerIDs(_tid_markers){
					console.log("updateTrackerIDs : INIT");

					try{
						$("#trackerID_selector option[value!='<?php echo $_config['default_trackerID']; ?>']").each(function() {
							$(this).remove();
						});

						if(typeof _tid_markers != "undefined" && _tid_markers != null) {
							trackerIDs = Object.keys(_tid_markers);

							$.each(trackerIDs, function( index, value ) {
								$('#trackerID_selector').append($('<option>', {
									value: value,
									text: value
								}));
							});

							$("#trackerID_selector").val(trackerID);
						}else{
							console.log("updateTrackerIDs : INFO no trackerID found in markers json");
							return;
						}

					}catch(err) {
						console.log("updateTrackerIDs : ERROR " + err.message);
						alert( err.message );
					}
				}

				/**
				* Draws a set of location tracks per tid in _tid_markers array
				* @param {Array} _tid_markers
				*/
				function drawMap(_tid_markers, is_live_map_update){
					console.log("drawMap : INIT");

					try{
						if((typeof _tid_markers == "undefined" || _tid_markers == null) && typeof tid_markers != "undefined" && tid_markers != null) {
							_tid_markers = tid_markers;
							console.log("drawMap : INFO null param given but global markers available !");
						}else if(typeof _tid_markers != "undefined" && _tid_markers != null) {
							tid_markers = _tid_markers;
							console.log("drawMap : INFO non null param given !");
						}else{
							if (!is_live_map_update){
								console.log("drawMap : ERROR null param given and global markers not available !");
								alert('No location markers collected for selected dates and accuracy !');
							}
							return false;
						}

						console.log("drawMap : INFO tid_markers = ");
						console.log(tid_markers);

						//vars for map bounding
						var max_lat = -1000;
						var min_lat = 1000;
						var max_lon = -1000;
						var min_lon = 1000;

						eraseMap();

						nb_markers=0; // global markers counter
						trackerIDs = Object.keys(_tid_markers);

						my_markers = [];
						my_latlngs = [];
						polylines = [];

						if(trackerIDs.length > 0){
							var markers, trackerIDString, popupString, newDate, removeString, my_marker;

							for ( j=0; j < trackerIDs.length; ++j ){
								tid = trackerIDs[j];
								markers = _tid_markers[tid];
								my_latlngs[tid] = [];
								my_markers[tid] = [];

								if(trackerID == tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){
									trackerIDString = ['Tracker ID', tid]

									if(markers.length > 0){
										for ( i=0; i < markers.length; ++i ) {
										 	nb_markers = nb_markers+1;

											popupString = [trackerIDString]

											if(markers[i].epoch != 0){
												newDate = new Date();
												newDate.setTime(markers[i].epoch * 1000);
												newDate = newDate.toLocaleString();

												popupString.unshift(['Time', newDate])
											}
											else {
												popupString.unshift(['Time', markers[i].dt])
											}

											popupString.push(['Accuracy', markers[i].accuracy + ' m'])

											if(markers[i].heading){
												popupString.push(['Heading', markers[i].heading + ' °'])
											}
											if(markers[i].velocity){
												popupString.push(['Velocity', markers[i].velocity + ' km/h'])
											}
											popupString.push(['Location', (markers[i].display_name)
												? markers[i].display_name
												: "<span id='loc_"+ i +"'><a role='button' onclick='geodecodeMarker("+ '"' + tid + '"' +", "+ i +");' title='Get location'>Get location</a></span>"
											])

											removeString = "<br/><br/><a role='button' onclick='deleteMarker("+ '"' + tid + '"' +", "+ i +");' title='Delete marker'>Delete marker</a>";

											//prepare popup HTML code for marker
											popupString = '<table width="300px">' + popupString.map(function(row){return '<tr valign="top"><td>' + row.join('</td><td>') + '</td></tr>';}).join("\n") + '</table>' + removeString;

										 	//create leaflet market object with custom icon based on tid index in array
									 		if(i == 0){
										 		//first marker
									 			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_start_icons[j]} ).bindPopup(popupString);
									 		}else if(i == markers.length-1){
										 		//last marker
									 			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_finish_icons[j]} ).bindPopup(popupString);
									 		}else{
										 		//all other markers
									 			my_marker = L.marker( [markers[i].latitude, markers[i].longitude], {icon: marker_icons[j]} ).bindPopup(popupString);
									 		}

									 		if(max_lat < markers[i].latitude) { max_lat = markers[i].latitude; }
									 		if(min_lat > markers[i].latitude) { min_lat = markers[i].latitude; }
									 		if(max_lon < markers[i].longitude) { max_lon = markers[i].longitude; }
									 		if(min_lon > markers[i].longitude) { min_lon = markers[i].longitude; }

									 		//add marker to map only if cookie 'show_markers' says to or if 1st or last marker
									 		if(show_markers != '0' || i == 0 || i == markers.length-1){
									 			my_marker.addTo( mymap );
									 		}

									 		/*
									 		//default show popup for last marker of track
									 		if(i == markers.length-1){
									 			my_marker.addTo( mymap ).openPopup();;
									 		}
									 		*/

									 		//collect all markers location to prepare drawing track, per trackerID
									 		my_latlngs[tid][i] = [markers[i].latitude, markers[i].longitude, i];

									 		//todo : onmouseover marker, display accuracy radius
											/*
											if(i+1 == markers.length && markers[i].acc > 0){
									 			L.circle(my_latlngs[i], {
														opacity: 0.2,
														radius: markers[i].acc
												}).addTo(mymap);
											}
											*/

											//array of all markers for display / hide markers + initial auto zoom scale
											my_markers[tid][i] = my_marker;
										}

										polylines[tid] = L.hotline(my_latlngs[tid], {
											min: 0,
											max: markers.length,
											palette: {
												"0.0": 'green',
												"0.5": 'yellow',
												"1.0": 'red'
											},
											weight: 4,
											outlineColor: '#000000',
											outlineWidth: 0.5
										}).addTo(mymap);
									}
									else if (!nb_markers && !is_live_map_update){
										console.log("drawMap : ERROR No location data for trackerID '" + trackerID + "' found !");
										alert('No location data for trackerID \'' + trackerID + '\' found !');
									}
								}
							}
						}
						else if (!nb_markers && !is_live_map_update){
							console.log("drawMap : ERROR No location data found for any trackerID !");
							alert('No location data found for any trackerID !');
						}

						//save default zoom scale
						setDefaultZoom();

						//auto zoom scale based on all markers location
						mymap.fitBounds([
							[min_lat, min_lon],
							[max_lat, max_lon]
						]);

					}catch(err) {
						if (!is_live_map_update){
							console.log("drawMap : ERROR " + err.message);
							alert( err.message );
						}
					}

					map_drawn = (nb_markers > 0);
					return map_drawn;
				}

				function setDefaultZoom(){
					console.log("setDefaultZoom : INIT");

					setTimeout(function() {
						default_zoom = mymap.getZoom();
						default_center = mymap.getCenter();
					}, 2000);
				}

				/**
				* Clears all markers on current map
				*/
				function eraseMap(){
					if (!map_drawn) return;

					console.log("eraseMap : INIT");

					$.each(trackerIDs, function(_index, _tid){
						if(_tid in polylines) { polylines[_tid].removeFrom(mymap); }
					});

					$.each(trackerIDs, function(_index, _tid){
						//if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){
							$.each(my_markers[_tid], function(_index2, _marker){
								_marker.remove();
							});
						//}
					});

					map_drawn = false;
				}

				/**
				* Displays Icons for each marker
				*/
				function showMarkers(){
					console.log("showMarkers : INIT");

					$.each(trackerIDs, function(_index, _tid){
						if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){
							$.each(my_markers[_tid], function(_index2, _marker){
								//add marker to map except first & last (never removed)
								if(_index2 != 0 || _index2 != my_markers[_tid].length){
									_marker.addTo( mymap );
								}
							});
						}
					});
					return true;
				}

				/**
				* Hide icons for each markers except 1st & last
				*/
				function hideMarkers(){
					console.log("hideMarkers : INIT");

					$.each(trackerIDs, function(_index, _tid){
						if(trackerID == _tid || trackerID == "<?php echo $_config['default_trackerID']; ?>"){
							$.each(my_markers[_tid], function(_index2, _marker){
								//remove marker except first & last
								if(_index2 > 0 && _index2 < my_markers[_tid].length-1){
									_marker.remove();
								}
							});
						}
					});
					return true;
				}

				/**
				* Toggle to display or hide icons for markers
				*/
				function showHideMarkers(){
					console.log("showHideMarkers : INIT");

					if($('#show_markers').hasClass( "btn-default" )){
						showMarkers();
						Cookies.set('show_markers', 1, { expires: 365 });
						show_markers = 1;
						$('#show_markers').removeClass( "btn-default" ).addClass( "btn-primary" ).addClass( "active" );
						return true;
					}else{
						hideMarkers();
						Cookies.set('show_markers', 0, { expires: 365 });
						show_markers = 0;
						$('#show_markers').removeClass("btn-primary").removeClass("active").addClass("btn-default");
						return true;
					}
				}

				/**
				* reset zoom to stored level at initial load/display
				*/
				function resetZoom(){
					console.log("resetZoom : INIT");
					mymap.setView(default_center, default_zoom);
					return false;
				}

				function drawAltitudeChart(){
				}

				function drawVelocityChart(){
				}

///// DATA HANDLERS
				/**
				* get the markers data from RPC and fires drawMap function if success
				*/
				function getMarkers(is_live_map_update){
					console.log("getMarkers : INIT");

					//ajax call to get list of markers
					$.ajax({
						url: 'rpc.php',
						data: {
							"password":    "<?php echo $_REQUEST['password']; ?>",
							"dateFrom":    dateFrom.clone().utc().format('YYYY-MM-DD'),
							"dateTo":      dateTo.clone().utc().format('YYYY-MM-DD'),
							"accuracy":    accuracy,
							//"trackerID": trackerID,
							//"epoc":      time(),
							"action":      'getMarkers'
						},
						type: 'GET',
		 				dataType: 'json',
						beforeSend: function(xhr){
							$('#mapid').css('filter','blur(5px)');
						},
						success: function(data, status){
							if(data.status){
								jsonMarkers = JSON.parse(data.markers);

								// don't process callback if timer has been cancelled
								if (is_live_map_update && !live_view_timer) return;

								if(handleMarkersData(jsonMarkers, is_live_map_update)){ $('#mapid').css('filter','blur(0px)'); }
							}else{
								console.log("getMarkers : ERROR Status : " + status);
								console.log("getMarkers : ERROR Data : ");
								console.log(data);
							}
						},
						error: function(xhr, desc, err){
							console.log(xhr);
							console.log("getMarkers : ERROR Details: " + desc + "\nError:" + err);
						}
					});
				}

				/* Call-back function following ajax call to get markers (json decoded)
				*/
				function handleMarkersData(_tid_markers, is_live_map_update){
					drawMap(_tid_markers, is_live_map_update);
					drawVelocityChart(_tid_markers);
					drawAltitudeChart(_tid_markers);
					updateTrackerIDs(_tid_markers);
					updateNavbarUI();
					return true;
				}

				/**
				* get human readable location information for a specific location marker
				*/
				function geodecodeMarker(tid, i){
					console.log("geodecodeMarker : INIT");
					console.log("geodecodeMarker : INFO Geodecoding marker #" + i);

					var my_marker = tid_markers[tid][i];

					//ajax call to geo-decode marker from backend
					$.ajax({
						url: 'rpc.php',
						data: {
							"password": "<?php echo $_REQUEST['password']; ?>",
							"epoch":    my_marker.epoch,
							"action":   'geoDecode'
						},
						type: 'get',
						dataType: 'json',
						success: function(data, status){
							if(data.status){
								console.log("geodecodeMarker : INFO Status : " + status);
								console.log("geodecodeMarker : INFO Data : " + data);

								//update marker data
								$('#loc_'+i).text(data.location);
							}else{
								console.log("geodecodeMarker : ERROR Status : " + status);
								console.log("geodecodeMarker : ERROR Data : " + data);
							}
						},
						error: function(xhr, desc, err){
							console.log(xhr);
							console.log("geodecodeMarker : ERROR Details: " + desc + "\nError:" + err);
						}
					});
				}

				/**
				* removes marker from map & deletes from DB
				*/
				function deleteMarker(tid, i){
					console.log("deleteMarker : INIT tid = "+tid+" i = "+i);

					var my_marker = tid_markers[tid][i];

					if(confirm('Do you really want to permanently delete marker ?')){
						console.log("deleteMarker : INFO Removing marker #" + i);

						//ajax call to remove marker from backend
						$.ajax({
							url: 'rpc.php',
							data: {
								"password": "<?php echo $_REQUEST['password']; ?>",
								"epoch":    my_marker.epoch,
								"action":   'deleteMarker'
							},
							type: 'get',
							dataType: 'json',
							success: function(data, status){
								if(data.status){
									//removing element from JS array
									tid_markers[tid].splice(i, 1);

									drawMap();
								}else{
									console.log("deleteMarker : ERROR Status : " + status);
									console.log("deleteMarker : ERROR Data : " + data);
								}
							},
							error: function(xhr, desc, err){
								console.log(xhr);
								console.log("deleteMarker : ERROR Details: " + desc + "\nError:" + err);
							}
						});
					}
				}
			</script>
		</div>
		<div class="container">
			<div id="mapid"></div>
			<div id="velocityChart"></div>
			<div id="altitudeChart"></div>
		</div>
	</body>
</html>
