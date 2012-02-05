var currentMarker = new google.maps.Marker(), currentPoly = new google.maps.Polygon(), currentInfo = -1, currentXHR = -1, openedInfoWindows = {}, infoWindowTimer = false, map = false, markers = new Array();

function changeMarker(overlayID) {
	marker = null;
	for (var i=0, length = markers.length; i< length;i++){
		if(overlayID == markers[i].id) {
			marker = markers[i];
			break;
		}
	}
	updateMarker(marker);

}

function updateMarker(marker){
	if (currentInfo != marker.id) {
		currentMarker.setIcon('http://www.google.com/mapfiles/marker.png');
		currentMarker.setZIndex(currentMarker.origZ);
		currentPoly.setOptions({fillColor: '#A84EF2'});
		if (marker.id.substr(0,5) == 'Point'){
			marker.setIcon('http://www.google.com/mapfiles/dd-start.png');
			currentMarker = marker;
			currentMarker.setZIndex(999);
			map.panTo(marker.getPosition());
		} else {
			marker.setOptions({fillColor: '#68BF4C'});
			currentPoly = marker;
			map.panTo(marker.infoWindow.getPosition());
		}
		getContent(marker.id);
		if (map.getZoom() == defaultZoom){
			map.setZoom(defaultZoom+1);
		}
		}
}

jQuery(document).ready(function() {
	var cacheImage = document.createElement('img');
	cacheImage.src = 'themes/seacs/images/ajax-loader.gif';
	
	jQuery('.left li').click(function(e) {
		e.preventDefault();
		jQuery('html, body').animate({scrollTop:'600px'}, 'fast');
	});
	jQuery('.left h2').click(function(e) {
		e.preventDefault();
		jQuery('html, body').animate({scrollTop:'600px'}, 'fast');
	});
	
	currentInfo = 'home';
	window.map = new google.maps.Map(jQuery('#map').get(0), {zoom:defaultZoom, center: center, mapTypeId:google.maps.MapTypeId.TERRAIN, streetViewControl:false});
	
	// Initialize markers
	jQuery.getJSON(window.location.pathname + '/getMarkers', function(data) {
		for (var i = 0, I = data.length; i < I; i++) {
			var marker = false;
			var labelNode = jQuery('<span class="gmapslabel labelproto">' + data[i][2] +'</span>');
			jQuery('#map').after(labelNode);
			var labelWidth = labelNode.width();
			var offset = Math.round(labelNode.width() / 2);
			labelNode.remove();
			
			marker = new MarkerWithLabel({position: new google.maps.LatLng(data[i][1][0], data[i][1][1]), labelContent: data[i][2], labelClass: 'gmapslabel', labelAnchor: new google.maps.Point(offset, 0), labelStyle: {width: labelWidth}, labelZIndex: 10});
			marker.setMap(map);
			marker.id = 'Point-' + data[i][0];
			marker.origZ = 10;
			window.markers.push(marker);
			
			google.maps.event.addListener(marker, 'mouseover', function(e) {
				this.setZIndex(9999);
				clearTimeout(infoWindowTimer);
				jQuery.each(openedInfoWindows, function(key, window) {window.close()});
			});
			
			google.maps.event.addListener(marker, 'mouseout', function() {
				this.setZIndex(this.origZ);
				infoWindowTimer = setTimeout(function() {
					jQuery.each(openedInfoWindows, function(key, window) {window.open(map)});
				}, 500);
			});
			
			google.maps.event.addListener(marker, 'click', function(){updateMarker(this)});
		};
		
	});
	
	// Initialize polygons
	jQuery.getJSON(window.location.pathname + '/getPolygons', function(data) {
		for (var i = 0, I = data.length; i < I; i++) {
			var polygon = false;
			path = [];
			
			for (var j = 3, J = data[i].length; j < J; j++) {
				var vertex = new google.maps.LatLng(data[i][j][0], data[i][j][1]);
				path.push(vertex);
			}
			
			// Make sure it's a valid polygon
			if (path.length < 3) continue;
			
			polygon = new google.maps.Polygon({
				paths: path,
				strokeColor: '#000000',
				strokeOpacity: 1,
				strokeWeight: 3,
				fillColor: '#A84EF2',
				fillOpacity: 0.5
			});

			polygon.setMap(map);
			polygon.id = 'Polygon-' + data[i][0];
			var polyCenter = new google.maps.LatLng(data[i][2][0], data[i][2][1])
			window.markers.push(polygon);
			
			polygon.infoWindow = new google.maps.InfoWindow({
				content: '<strong>' + data[i][1] + '</strong>',
				disableAutoPan: true,
				maxWidth: 200,
				position: polyCenter
			});
			
			google.maps.event.addListener(polygon, 'click', function() {updateMarker(this)});
			
			google.maps.event.addListener(polygon, 'mouseover', function() {
				this.infoWindow.open(map);
				openedInfoWindows[this.id] = this.infoWindow;
			});
			
			google.maps.event.addListener(polygon, 'mouseout', function() {
				this.infoWindow.close();
				delete openedInfoWindows[this.id];
			});
		}
	});
});
