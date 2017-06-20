/*jslint browser:true devel:true*/
/*global self, Promise, resolve*/

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
            if (xhr.status >= 400) {
                self.postMessage("There was a permanent error uploading a response.");
                return;
            }
        } catch (e) {
            self.postMessage("There was a temporary error uploading a response. Retrying...");
        }
    }
    self.postMessage("");
}

self.addEventListener('message', function (e) {
    'use strict';
    self.postMessage("Some submissions are still being uploaded.");
    upload(e.data);
}, false);
