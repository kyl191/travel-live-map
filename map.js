var currentXHR = -1, map = false, vertexes = [];

jQuery(document).ready(function() {
	var center = new google.maps.LatLng( 36.34355, 141.211575);
	window.map = new google.maps.Map(jQuery('#map').get(0), {zoom:5, center: center, mapTypeId:google.maps.MapTypeId.TERRAIN, streetViewControl:false});
	
	// Initialize markers
	jQuery.getJSON("load.php", function(data) {
		for (var i = 0, I = data.length; i < I; i++) {
			var pos = new google.maps.LatLng(data[i][1][0], data[i][1][1]);
			vertexes.push(pos);
			var marker = new Marker({position: pos, title: data[i][2]});
			marker.setMap(map);
		};

		var polyline = new google.maps.Polyline({
      		path: vertexes,
      		strokeColor: "#FF0000",
      		strokeOpacity: 1.0,
      		strokeWeight: 2
    	});

    	polyline.setMap(map);

		
	});

});
