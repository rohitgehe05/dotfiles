/**
 * LeadsFlow Pro Pro
 * (c) IMSuccessCenter.com, 2015
 */

 
jQuery(document).ready(function($){
  //Check if Dashboard is active  
  if ( $( "#world_map" ).length ) {	
		  map_obj = $('#world_map');
		  map_obj.gmap3({map: { options: {
			  maxZoom: 3,
			  minZoom: 2,
			  mapTypeControl: false,
			  navigationControl: false,
			  scrollwheel: true,
			  streetViewControl: false,
			  panControl: false,
			  zoomControl: false,
			  scaleControl: false,
			  overviewMapControl: false,
			  styles: [
    {
        "featureType": "all",
        "elementType": "labels.text.fill",
        "stylers": [
            {
                "saturation": 36
            },
            {
                "color": "#333333"
            },
            {
                "lightness": 40
            }
        ]
    },
    {
        "featureType": "all",
        "elementType": "labels.text.stroke",
        "stylers": [
            {
                "visibility": "on"
            },
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "all",
        "elementType": "labels.icon",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "administrative",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#fefefe"
            },
            {
                "lightness": 17
            },
            {
                "weight": 1.2
            }
        ]
    },
    {
        "featureType": "administrative.country",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#e4e4e4"
            }
        ]
    },
    {
        "featureType": "landscape",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 20
            }
        ]
    },
    {
        "featureType": "poi",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f5f5f5"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "featureType": "poi.park",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#dedede"
            },
            {
                "lightness": 21
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.fill",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 17
            }
        ]
    },
    {
        "featureType": "road.highway",
        "elementType": "geometry.stroke",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 29
            },
            {
                "weight": 0.2
            }
        ]
    },
    {
        "featureType": "road.arterial",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 18
            }
        ]
    },
    {
        "featureType": "road.local",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#ffffff"
            },
            {
                "lightness": 16
            }
        ]
    },
    {
        "featureType": "transit",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#f2f2f2"
            },
            {
                "lightness": 19
            }
        ]
    },
    {
        "featureType": "water",
        "elementType": "geometry",
        "stylers": [
            {
                "color": "#e9e9e9"
            },
            {
                "lightness": 17
            }
        ]
    }
]
			  }  
		  } });
		  
		  wf_opt_add_markers(wf_opt_map_markers, 0);
		  
		
		  countries = new Array();
		  countries.push(['Country', 'Number of Subscribers']);
		  $.each(wf_opt_countries, function(country, tmp_data) {
		   countries.push([country, parseInt(tmp_data)]);
		   
		  });
		  
		  
		  optins_days = new Array();
		  optins_days.push(['Date', 'Views', 'Conversions']);
		  $.each(wf_opt_optin_stats.history, function(date, tmp_data) {
			  optins_days.push([date, parseInt(tmp_data.views), parseInt(tmp_data.conversions)]);
		  });
		  
		  
		  optins_top = new Array();
		  optins_top.push(['Optin', 'Views', 'Conversions']);
		  $.each(wf_opt_top_optins, function(date, tmp_data) {
			  optins_top.push([tmp_data.optin, parseInt(tmp_data.views), parseInt(tmp_data.conversions)]);
		  });
		  
		  subscriber_growth = new Array();
		  subscriber_growth.push(['Date', 'Total']);
		  $.each(wf_opt_sub_growth, function(date, count) {
			  subscriber_growth.push([date, parseInt(count)]);
		  });
		  
		  google.charts.load('current', {'packages': ['corechart', 'bar', 'line']});
		  
		  
		  		  		  		  
		  function drawSubGrowthStats() {
			  var data = new google.visualization.arrayToDataTable(subscriber_growth);
			  
		
			  var options = {
				colors: ['#D80000'],
				chartArea: {width: '85%'},
				hAxis: {showTextEvery: Math.round(subscriber_growth.length*0.1) },
                vAxis: {showTextEvery: 5, format: '0' },
				legend: {position:'none'}
			  };
		
			  var chart = new google.visualization.AreaChart(
				document.getElementById('subgrowth_chart'));
		
			  chart.draw(data, options);
			}
			google.charts.setOnLoadCallback(drawSubGrowthStats);
		  
		  
		 
			function drawOptinsStats() {
			  var data = new google.visualization.arrayToDataTable(optins_days);
			  
		
			  var options = {
				colors: ['#7d7d7d', '#D80000'],
				chartArea: {width: '80%'},
				hAxis: {showTextEvery:3},
				legend: 'top',
				series: {
				  0: {targetAxisIndex: 0},
				  1: {targetAxisIndex: 1}
				},
				vAxes: {
				  0: {title: 'Views'},
				  1: {title: 'Conversions'},
				},
				vAxis: {format: '0', minValue: 1, viewWindowMode:'explicit' }
			  };
		
			  var chart = new google.visualization.AreaChart(
				document.getElementById('optins_chart'));
		
			  chart.draw(data, options);
			}
			google.charts.setOnLoadCallback(drawOptinsStats);
			
			
			  function drawChart() {
				var data = google.visualization.arrayToDataTable(countries);
				var options = {
				  _title: 'Subscribers per Country',
				  legend: 'none',
				  pieSliceText: 'label',
				  chartArea:{left:0,top:0,width:'100%',height:'100%'},
				  slices: {
					0: { color: '#FD151B' },
					1: { color: '#437F97' },
					2: { color: '#849324' },
					3: { color: '#FFB30F' },
					4: { color: '#01295F' },
				  }
				};
		
				var chart = new google.visualization.PieChart(document.getElementById('countries_pie'));
				chart.draw(data, options);
			  }
				
			  google.charts.setOnLoadCallback(drawChart);
			  
			  function drawTopOptins() {
					 var data = google.visualization.arrayToDataTable(optins_top);
								
					  var options = {
						title: '',
						colors: ['#7d7d7d', '#D80000'],
						chartArea: {width: '50%'},
						hAxis: {
						  title: '',
						  minValue: 0
						},
						vAxis: {
						  title: 'Optin'
						}
					  };
				
					  var chart = new google.visualization.BarChart(document.getElementById('optins_top'));
					  chart.draw(data, options);
					}
							  
					google.charts.setOnLoadCallback(drawTopOptins);		  
							  
							  $(window).resize(function(){
								  drawChart();
								  drawTopOptins();
								  drawOptinsStats();
								  drawSubGrowthStats();
								});
			  
			  
		
				
		function wf_opt_map_pin_click(marker, event, data) {
		  var map = jQuery('#world_map').gmap3('get');
		  var infowindow = jQuery('#world_map').gmap3({get:{name:'infowindow', tag: 'info'}});
		
		  if (!data) {
			return;
		  }
		
		  data = '<div class="lf_infowindow">' + data + '</div>';
		
		  if (infowindow){
			infowindow.open(map, marker);
			infowindow.setContent(data);
		  } else {
			jQuery('#world_map').gmap3({ infowindow:{tag: 'info', anchor: marker, options:{content: data} } });
		  }
		} // lf_map_pin_click
		
		
		function wf_opt_add_markers(markers, animation) {
		  if (markers.length == 0) {
			return;
		  }
			
		  tmp_pins = [];
		  jQuery(markers).each(function(ind, pin) {
			if (animation) {
			  tmp_pins.push({latLng:[pin.lat, pin.lng], options:{title: pin.address + '; ' + pin.timestamp_diff + ' ago', _icon: 'x', animation: 1, show_description: 1, data: pin.address + '; ' + pin.timestamp_diff + ' ago' }});  
			} else {
			  tmp_pins.push({latLng:[pin.lat, pin.lng], options:{icon : new google.maps.MarkerImage(wf_opt_plugin_url+'/images/map_pin_red.png'), title: pin.address + '; ' + pin.timestamp_diff + ' ago', _icon: 'x', animation: 0, show_description: 1, data: pin.address + '; ' + pin.timestamp_diff + ' ago' }});
			}
		  });
			
		  map_obj.gmap3({ marker:{ values: tmp_pins, events:{ click: function(marker, event, context){ wf_opt_map_pin_click(marker, event, marker.data); } } }});
		  map_obj.gmap3({ autofit:{} });
		  map_obj.gmap3({trigger: 'resize'});
          
          map_obj.gmap3({ map:{ options:{ scrollwheel: false,
                                  zoomControl: false,
                                  panControl: true,
                                  draggable: true,
                                  mapTypeControl: false,
                                  disableDoubleClickZoom: false,
                                  keyboardShortcuts: true,
                                  streetViewControl: false
  } } });
		  
		  // disable animation after 5sec
		  setTimeout(function(){ map_obj.gmap3({
			get: { name: "marker",
				   _tag: "new",
				   all: true,
				   callback: function(objs){
					 jQuery.each(objs, function(i, obj){
					  obj.setAnimation(0); });
				   }
				 }
			});
		  }, 5000);
		} // lf_add_markers

  } // END Check if Dashboard is active  
}); // onload
