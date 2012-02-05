var currentXHR = -1, map = false, vertexes = [], recent = false;

jQuery(document).ready(function() {
	var center = new google.maps.LatLng( 36.34355, 141.211575);
	window.map = new google.maps.Map(jQuery('#map').get(0), {zoom:5, center: center, mapTypeId:google.maps.MapTypeId.TERRAIN, streetViewControl:false});
	
	// Initialize markers
	jQuery.getJSON("load.php", function(data) {
		for (var i = 0, I = data.length; i < I; i++) {
			var pos = new google.maps.LatLng(data[i]['lat'], data[i]['long']);
			vertexes.push(pos);
			var marker = new google.maps.Marker({position: pos, title: data[i]['timestamp']});
			marker.setMap(window.map);
			recent = marker;
		};
		recent.setIcon("http://www.google.com/mapfiles/dd-start.png");
		recent.setZIndex(999);
		$('#map').after("<p>Most recent update: " + recent.getTitle() + "</p><a href=\"#\" id=\"gotolink\">Go to most recent location</a>");
		jQuery('#gotolink').click(function(e) {
			e.preventDefault();
			window.map.panTo(recent.getPosition());
			window.map.setZoom(15);
		});

		var polyline = new google.maps.Polyline({
      		path: vertexes,
      		strokeColor: "#FF0000",
      		strokeOpacity: 1.0,
      		strokeWeight: 2
    	});

    	polyline.setMap(window.map);

		
	});

});
