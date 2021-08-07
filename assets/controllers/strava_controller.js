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
    static targets = [ "clientId" ]

    chooseAthlete(event){
        console.log('clientId: '.concat(this.clientIdTarget.value) );
        console.log(document.location.href);
        //document.location.href = document.location.href.concat(this.clientIdTarget.value);
    }
}
