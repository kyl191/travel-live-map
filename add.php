<html>
<head>
<link rel="stylesheet" type="text/css" href="map.css" />
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true&libraries=geometry"></script>
<script type="text/javascript" src="map.js"></script>
<script type="text/javascript">
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position){
        var latitude = position.coords.latitude;
        var longitude = position.coords.longitude;
        var coords = new google.maps.LatLng(latitude, longitude);
        var mapOptions = {
            zoom: 15,
            center: coords,
            mapTypeControl: true,
            navigationControlOptions: {
                style: google.maps.NavigationControlStyle.SMALL
            },
            mapTypeId: google.maps.MapTypeId.TERRAIN
            };
        map = new google.maps.Map(jQuery('#map').get(0), mapOptions);
        var marker = new google.maps.Marker({
                    position: coords,
                    map: map,
                    title: "Your current location!"
            });

        map.panTo(marker.getPosition());
        $.ajax({
            url: "save.php", 
            data: {lat: latitude, long: longitude}, 
            error: function(data){
                alert("Error uploading: " + data);
            }
        });

 
        });
    }else {
        // Do nothing.
    }
	</script>
</head>
<body>
<div id="map"></div>
</body>
</html> 