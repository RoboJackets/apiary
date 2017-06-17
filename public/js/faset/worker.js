/*jslint browser:true devel:true*/
/*global self, Promise, resolve*/

var queueLength = 0;

function upload(data) {
    "use strict";
    var xhr = new XMLHttpRequest();
    while (xhr.status !== 200) {
        xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/v1/faset", false);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.timeout = 5000;
        try {
            xhr.send(data);
        } catch (ignore) {

        }
    }
    self.postMessage("");
}

self.addEventListener('message', function (e) {
    'use strict';
    queueLength += 1;
    self.postMessage("Some submissions are being uploaded.");
    upload(e.data);
}, false);
