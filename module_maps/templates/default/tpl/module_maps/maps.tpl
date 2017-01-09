<!-- see section "Template-API" of module manual for a list of available placeholders -->
<!-- ATTENTION: Please respect the terms of use of the Google Maps API. Set up your own api key in the config file -->

<!-- available placeholders: address, lat, lng, infotext, systemid -->
<map>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=%%apikey%%"></script>
    <script type="text/javascript">
        $(function () {
            var map;
            var startPos = new google.maps.LatLng('%%lat%%', '%%lng%%');

            var mapOptions = {
                zoom: 10,
                center: startPos,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            map = new google.maps.Map(document.getElementById('map_canvas_%%systemid%%'), mapOptions);

            var marker = new google.maps.Marker({
                position: startPos,
                map: map
            });

            var infotext = '%%infotext%%';
            if (infotext.length != 0) {
                var infoWindow = new google.maps.InfoWindow({
                	position: startPos,
                	content: '<div class="infoWindow">'+infotext+'</div>'
                });
                infoWindow.open(map);

                google.maps.event.addListener(marker, 'click', function() {
                    infoWindow.open(map);
                });
            }
        });
    </script>

    <div id="map_canvas_%%systemid%%" class="element_maps" style="width: 600px; height: 480px;"></div>
</map>