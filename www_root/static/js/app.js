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
var tid_markers = []; // markers collected from json
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

  dateFrom = window.config.dateFrom ? moment(window.config.dateFrom) : null;
  dateTo   = window.config.dateTo   ? moment(window.config.dateTo)   : null;

  if (!dateFrom || !dateFrom.isValid()) dateFrom = dateTodayNoon;
  if (!dateTo   || !dateTo.isValid())   dateTo   = dateTodayNoon;

  $('#dateFrom').val(dateFrom.format('YYYY-MM-DD'));
  $('#dateTo').val(dateTo.format('YYYY-MM-DD'));

  $('.input-daterange').datepicker({
    format:   'yyyy-mm-dd',
    endDate:  '0d',
    language: window.config.language
  });

  $('.input-daterange').datepicker().on('hide', function(e) {
    return gotoDate($('#dateFrom').val(), $('#dateTo').val());
  });

  //accuracy event handlers
  accuracy = window.config.accuracy;
  $('#accuracy').change(function(){
    gotoAccuracy();
  });
  $('#accuracySubmit').click(function(){
    gotoAccuracy();
  });

  //trackerID event handlers
  trackerID = window.config.trackerID;

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
    window.location.pathname + '?password=' + window.config.password + '&dateFrom=' + moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD')
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

    //location.href = './?password=' + window.config.password + '&dateFrom=' + moment(dateFrom).format('YYYY-MM-DD') + '&dateTo=' + moment(dateTo).format('YYYY-MM-DD') + '&accuracy=' + _accuracy + '&trackerID=' + trackerID;

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
    live_view_timer = setInterval(handleLiveMap, window.config.live_map_interval);
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
    $("#trackerID_selector option[value!='" + window.config.default_trackerID + "']").each(function() {
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

    nb_markers = 0; // global markers counter
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

        if(trackerID == tid || trackerID == window.config.default_trackerID){
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
    //if(trackerID == _tid || trackerID == window.config.default_trackerID){
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
    if(trackerID == _tid || trackerID == window.config.default_trackerID){
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
    if(trackerID == _tid || trackerID == window.config.default_trackerID){
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
      "password":  window.config.password,
      "dateFrom":  dateFrom.clone().utc().format('YYYY-MM-DD'),
      "dateTo":    dateTo.clone().utc().format('YYYY-MM-DD'),
      "accuracy":  accuracy,
      //"trackerID": trackerID,
      //"epoc":    time(),
      "action":    'getMarkers'
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
      "password": window.config.password,
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
        "password": window.config.password,
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
