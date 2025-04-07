import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';
import './app.js';
import './styles/resize.css';
import Swal from 'sweetalert2';



// Select the file input and use 
// create() to turn it into a pond
FilePond.create(
    document.querySelector('input'),
    {
        labelIdle: `Drag & Drop your picture or <span class="filepond--label-action">Browse</span>`,
        imagePreviewHeight: 170,
        imageCropAspectRatio: '1:1',
        imageResizeTargetWidth: 200,
        imageResizeTargetHeight: 200,
        stylePanelLayout: 'compact circle',
        styleLoadIndicatorPosition: 'center bottom',
        styleProgressIndicatorPosition: 'right bottom',
        styleButtonRemoveItemPosition: 'left bottom',
        styleButtonProcessItemPosition: 'right bottom',
        server: {

            process: async (fieldName, file, metadata, load, error, progress, abort, transfer, options) => {
                const ret = await Swal.fire({
                    title: "Choose size?",
                    icon: "question",
                    input: "range",
                    inputAttributes: {
                        min: "10",
                        max: "300",
                        step: "5"
                    },
                    inputValue: 100,
                    showCancelButton: true,
                    cancelButtonText: "Cancel",
                });

                if (ret.isDismissed) {
                    abort();
                    Swal.fire("Cancelled", "Cancelled", "error");
                    return;
                }

                const formData = new FormData();
                formData.append(fieldName, file, file.name);

                const request = new XMLHttpRequest();
                request.open('POST', 'url-to-api');

                // Should call the progress method to update the progress to 100% before calling load
                // Setting computable to false switches the loading indicator to infinite mode
                request.upload.onprogress = (e) => {
                    progress(e.lengthComputable, e.loaded, e.total);
                };

                // Should call the load method when done and pass the returned server file id
                // this server file id is then used later on when reverting or restoring a file
                // so your server knows which file to return without exposing that info to the client
                request.onload = function () {
                    if (request.status >= 200 && request.status < 300) {
                        // the load method accepts either a string (id) or an object
                        load(request.responseText);
                    } else {
                        // Can call the error method if something is wrong, should exit after
                        error('oh no');
                    }
                };

                request.send(formData);

                // Should expose an abort method so the request can be cancelled
                return {
                    abort: () => {
                        // This function is entered if the user has tapped the cancel button
                        request.abort();

                        // Let FilePond know the request has been cancelled
                        abort();
                    },
                };
            },


        },
    }
);