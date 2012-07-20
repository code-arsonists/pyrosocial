(function($) {
    $.fn.staticgmapzoom = function(coords, options) {
        var opts = $.extend({}, $.fn.staticgmapzoom.defaults, options);

        return this.each(function() {
            $this = $(this);
            $this.css({
                width: opts.mapWidth,
                height: opts.mapHeight,
                position: 'relative'
            });
            var maps = '';
            var gmapZoomedIn = false;
            var gmapXY = $this.offset();

            maps+=$.fn.staticgmapzoom.getStaticMap(coords,0,opts);
            maps+=$.fn.staticgmapzoom.getStaticMap(coords,1,opts);
            maps+=$.fn.staticgmapzoom.getStaticMap(coords,2,opts);
            $this.html(maps);

            var gmapLayer1 = $this.find('div').eq(0);
            var gmapLayer2 = $this.find('div').eq(1);

            $this.hover(function(){
                gmapLayer1.stop(true,true).fadeOut('slow');
            },
            function(){
                gmapLayer1.stop(true,true).fadeIn('slow');
            });

            $this.bind('mousemove', function(event) {
                var mX = (event.pageX-gmapXY.left);
                var mY = (event.pageY-gmapXY.top);
                if(Math.abs((opts.mapWidth/2)-mX) <= (opts.mapWidth*opts.zoomInDistance) && Math.abs((opts.mapHeight/2)-mY) <= (opts.mapHeight*opts.zoomInDistance)) {
                    if(!gmapZoomedIn) {
                        gmapLayer2.stop(true,true).fadeOut('slow');
                        gmapZoomedIn = true;
                    }
                }
                else {
                    if(gmapZoomedIn) {
                        gmapLayer2.stop(true,true).fadeIn('slow');
                        gmapZoomedIn = false;
                    }
                }
            });

        });
    };
    $.fn.staticgmapzoom.getStaticMap = function(coords,level,opts) {
        var o = '<div style="position:absolute;left:0;top:0;z-index:'+(11-level)+';" class="gmaplayer'+level+'">';
        o = o+'<img src="http://maps.google.com/maps/api/staticmap?size='+opts.mapWidth+'x'+opts.mapHeight;
        o = o+'&maptype='+opts.mapType;
        o = o+'&zoom='+opts.mapZoom[level]+'&markers=color:green|size:large|'+coords+'&sensor=false"></div>';
        return o;
    };
    $.fn.staticgmapzoom.defaults = {
        mapType: 'roadmap',
        mapWidth : 400,
        mapHeight : 200,
        mapZoom : [12, 15, 18],
        zoomInDistance : .25
    };
})(jQuery);


Number.prototype.toRad = function() {
    return this * Math.PI / 180;
}
function deg2rad (angle) {
    return (angle / 180) * Math.PI;
}

Number.prototype.toDeg = function() {
    return this * 180 / Math.PI;
}
function rad2deg (angle) {
    return angle * 57.29577951308232; // angle / Math.PI * 180}
}



var pLocations = {
    self: this,
    obj: {},
    opts: {
        radius: 50,
        map:{
            location: false,
            zoom: 17,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
    },
    bounds: new google.maps.LatLngBounds(),
    loc: google.loader.ClientLocation,

    setOpt: function (key, val){
        this.opts[key] = val;
    },

    getOpt: function(key){
        return this.opts[key];
    },

    setLatLng: function (latlng){
        this.opts.map.center = latlng;

        lat = latlng.lat();
        lon = latlng.lng();

        $('#gmap').staticgmapzoom(lat+','+lon);

        dist = this.opts.radius;  // radius of bounding circle in kilometers
        //$radius = 6371;  // earth's radius, km
        radius = 3959;  // earth's radius, miles

        // first-cut bounding box (in degrees)
        lat1 = lat + rad2deg(dist/radius);
        lat2 = lat - rad2deg(dist/radius);
        // compensate for degrees longitude getting smaller with increasing latitude
        lon1 = lon + rad2deg(dist / radius / Math.cos(deg2rad(lat)));
        lon2 = lon - rad2deg(dist / radius / Math.cos(deg2rad(lat)));

        // convert origin of filter circle to radians
        $lat = deg2rad(lat);
        $lon = deg2rad(lon);


        this.bounds.extend(new google.maps.LatLng(lat1, lon1));
        this.bounds.extend(new google.maps.LatLng(lat2, lon2));
        this.obj.map.fitBounds(this.bounds);
    },

    getLatLng: function(){
        return this.opts.map.center;
    },
    /*		http://maps.googleapis.com/maps/api/staticmap?center=63.259591,-144.667969&zoom=6&size=400x400\
&markers=color:blue%7Clabel:S%7C62.107733,-145.541936&markers=size:tiny%7Ccolor:green%7CDelta+Junction,AK\
&markers=size:mid%7Ccolor:0xFFFF00%7Clabel:C%7CTok,AK&sensor=false" />
*/

    initialize: function (options) {
        $.extend(this.opts, options);

        this.obj.map = new google.maps.Map(document.getElementById("map_canvas"), this.opts.map);

        //GEOCODER
        this.obj.geocoder = new google.maps.Geocoder();

        this.opts.city = this.loc.address.city;
        this.opts.state = this.loc.address.region;
        $('#searchTextField').attr('placeholder','Search places near '+this.opts.city+', '+this.opts.state);
        //MAP
        if (!this.opts.location) this.setLatLng(new google.maps.LatLng(this.loc.latitude, this.loc.longitude));
        else this.setLatLng(this.opts.location);

        $("#address").val(this.opts.city+', '+this.opts.state);

        this.obj.marker = new google.maps.Marker({
            map: this.obj.map,
            draggable: true
        });
        var input = document.getElementById('searchTextField');
        this.obj.autocomplete = new google.maps.places.Autocomplete(input, {
            bounds:this.bounds
        });

        this.obj.autocomplete.bindTo('bounds', this.obj.map);

        this.obj.infowin = new google.maps.InfoWindow();
        var self = this;
        /****************** BOUND EVENT PLACE_CHANGED *******************/
        $('#searchTextField').live('keyup', function(){
				var v = $(this).val().replace(self.opts.city+', '+self.opts.state, '');
				$(this).val(v)


			});
        google.maps.event.addListener(this.obj.autocomplete, 'place_changed', function() {
            pLocations.obj.infowin.close();
            var place = this.getPlace();
            if (place.geometry.viewport)
            {
                pLocations.obj.map.fitBounds(place.geometry.viewport);
            } else {
                pLocations.obj.map.setCenter(place.geometry.location);
                pLocations.obj.map.setZoom(17);  // Why 17? Because it looks good.
            }

            /*var image = new google.maps.MarkerImage(
					place.icon,
					new google.maps.Size(71, 71),
					new google.maps.Point(0, 0),
					new google.maps.Point(17, 34),
					new google.maps.Size(35, 35)
				);
				pLocations.obj.marker.setIcon(image);*/
            pLocations.obj.marker.setPosition(place.geometry.location);
            pLocations.setLatLng(place.geometry.location);
            pLocations.obj.map.setZoom(17);  // Why 17? Because it looks good.

            var address = '';
            if (place.address_components)
            {
                address = [(place.address_components[0] && place.address_components[0].short_name || ''),
                (place.address_components[1] && place.address_components[1].short_name || ''),
                (place.address_components[2] &&	place.address_components[2].short_name || '')].join(' ');
            }

            pLocations.obj.infowin.setContent('<div><img style="float:left; margin: 4px 4px 4px 0;" src="http://cbk0.google.com/cbk?output=thumbnail&w=90&h=68&ll='+place.geometry.location.lat()+','+place.geometry.location.lng()+'"><strong>' + place.name + '</strong><br>' + address + '</div>');
            pLocations.obj.infowin.open(pLocations.obj.map, pLocations.obj.marker);
        });
        /****************** END BOUND EVENT PLACE_CHANGED *******************/

        // Sets a listener on a radio button to change the filter type on Places
        // Autocomplete.
        function setupClickListener(id, types) {
            var radioButton = document.getElementById(id);
            google.maps.event.addDomListener(radioButton, 'click', function() {
                pLocations.obj.autocomplete.setTypes(types);
            });
        }

        setupClickListener('changetype-all', []);
        setupClickListener('changetype-establishment', ['establishment']);
        setupClickListener('changetype-geocode', ['geocode']);
        $("#address").autocomplete({
            //This bit uses the geocoder to fetch address values
            source: function(request, response) {
                pLocations.obj.geocoder.geocode( {
                    'address': request.term,
                    'region': 'US'
                }, function(results, status) {
                    response($.map(results, function(item) {
                        _reg = {
                            label:  item.formatted_address,
                            value: item.formatted_address,
                            latitude: item.geometry.location.lat(),
                            longitude: item.geometry.location.lng()
                        };

                        _comp = item.address_components;
                        comp_count = item.address_components.length;

                        for (i=0; i < comp_count; i++)
                        {
                            // alert(_comp[i].types[0]);
                            switch (_comp[i].types[0])
                            {
                                case "subpremise":
                                    _reg.unit = _comp[i].short_name;
                                    break;

                                case "route":
                                    _reg.street = _comp[i].short_name;
                                    break;

                                case "street_number":
                                    _reg.address_num = _comp[i].short_name;
                                    break;
                                case "locality":
                                    _reg.city = _comp[i].short_name;
                                    break;

                                case "sublocality":
                                    //alert(_comp[i].short_name);
                                    _reg.city = _comp[i].short_name;
                                    break;

                                case "administrative_area_level_1":
                                    _reg.state = _comp[i].short_name;
                                    break;

                                case "administrative_area_level_2":
                                    _reg.county = _comp[i].short_name;
                                    break;

                                case "country":
                                    _reg.country = _comp[i].short_name;
                                    break;

                                case "postal_code":
                                    //alert(_comp[i].short_name);
                                    _reg.zipcode = _comp[i].short_name;
                                    break;
                            }
                        }
                        return _reg;
                    }));
                })
            },
            //This bit is executed upon selection of an address
            select: function(event, ui) {
                $("#latitude").val(ui.item.latitude);
                $("#longitude").val(ui.item.longitude);

                $("#loc_city").val(ui.item.city);
                $("#loc_state").val(ui.item.state);
                $("#loc_zipcode").val(ui.item.zipcode);
                if (ui.item.address_num != undefined && ui.item.street != undefined) $("#loc_address").val(ui.item.address_num+' '+ui.item.street);
                $("#loc_address2").val(ui.item.unit);
                $('#searchTextField').attr('placeholder','Search places near '+ui.item.city+', '+ui.item.state);
                //$("#loc_address2").val(ui.item.address2);
                //$("#loc_state").val(ui.item.address2);
                var location = new google.maps.LatLng(ui.item.latitude, ui.item.longitude);
                pLocations.obj.marker.setPosition(location);
                pLocations.obj.map.setCenter(location);
                pLocations.obj.autocomplete.setBounds(pLocations.obj.map.getBounds());
            }
        });


    //Add listener to marker for reverse geocoding
    /*google.maps.event.addListener(marker, 'drag', function() {
			geocoder.geocode({'latLng': marker.getPosition()}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						$('#address').val(results[0].formatted_address);
						$('#latitude').val(marker.getPosition().lat());
						$('#longitude').val(marker.getPosition().lng());
					}
				}
			});*/

    }
};
jQuery(document).ready( function() {
    pLocations.loc = google.loader.ClientLocation;
    pLocations.initialize();

});

