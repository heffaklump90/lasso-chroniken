import { Controller } from 'stimulus';

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
    connect() {
        let xhttp = new XMLHttpRequest();
        let _URL = new URL("https://www.strava.com/oauth/authorize");
        _URL.searchParams.set('client_id', 68910);
        _URL.searchParams.set('redirect_uri', 'https://localhost:8000');
        _URL.searchParams.set('response_type', 'code');
        xhttp.open("GET", _URL.toString(), false);
        let response = xhttp.send();
        this.element.content = response;
    }

}
