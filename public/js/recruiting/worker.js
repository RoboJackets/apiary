/*jslint browser:true devel:true*/
/*global self, Promise, resolve*/

function upload(data) {
    "use strict";
    let xhr = new XMLHttpRequest();
    while (xhr.status !== 200) {
        xhr = new XMLHttpRequest();
        xhr.open("POST", "/api/v1/recruiting", false);
        xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        xhr.setRequestHeader("Accept", "application/json");
        xhr.timeout = 5000;
        try {
            xhr.send(data);
            if (xhr.status >= 400) {
                self.postMessage("There was a permanent error uploading a response.");
                console.log("Server Response: " + xhr.responseText);
                return;
            } else if (xhr.status == 302) {
                let url = xhr.getResponseHeader('Location');
                console.log("Server Response: 302 Redirect to " + url)
                return;
            } else {
                self.postMessage("There was an unknown error uploading a response.");
                console.log("Server Response: " + xhr.responseText);
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
