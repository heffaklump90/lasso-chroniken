import { Controller } from 'stimulus';
import { Loader } from '@googlemaps/js-api-loader';
import { encode, decode } from '@googlemaps/polyline-codec';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    static values = {
        summaryPolyline: String,
        polyline: String,
        zoom: Number,
        height: String
    }

    static targets = [
        "mapview",
    ]

    initialize(){
        let map;
        const loader = new Loader({
            apiKey: "AIzaSyAHyJMvid_m5ST8tlDChKXuTfIM91xCStY",
            version: "weekly",
            language: "de",
        });
        loader.load().then(() => {

            const _path = decode(this.summaryPolylineValue, 5);

            const xArray = _path.map(function(x){
                return x[0];
            });
            const yArray = _path.map(function(x){
                return x[1];
            });
            const middleX = (Math.max(...xArray) + Math.min(...xArray)) / 2;
            const middleY = (Math.max(...yArray) + Math.min(...yArray)) / 2;
            const middle = new google.maps.LatLng(middleX, middleY);

            const coordinates = new google.maps.MVCArray();
            for(const tuple of _path){
                coordinates.push( new google.maps.LatLng(tuple[0], tuple[1]));
            }

            this.mapviewTarget.style = "height: ".concat(this.heightValue);

            map = new google.maps.Map(document.getElementById("map"), {
                center: middle,
                zoom: this.zoomValue
            });

            let runPath = new google.maps.Polyline({
                path: coordinates,
                strokeColor: "#AA1010",
                strokeOpacity: 1.0,
                strokeWeight: 2
            });
            runPath.setMap(map);
        });

    }

}
