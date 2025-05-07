import * as FilePond from 'filepond';
import 'filepond/dist/filepond.min.css';
import './app.js';
import './styles/resize.css';
import Swal from 'sweetalert2';



// Select the file input and use 
// create() to turn it into a pond
const filepond = FilePond.create(
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
        instantUpload: false,
        server: {

            process: async (fieldName, file, _metadata, _load, _error, _progress, abort, _transfer, _options) => {
                const ret = await sweetChooseSize();
                if (ret.isDismissed) {
                    abort();
                    Swal.fire("Cancelled", "Cancelled", "error");
                    return;
                }

                const formData = new FormData();
                formData.append(fieldName, file, file.name);

                const request = new Request(`/file/resize/${ret.value}`, {
                    method: "POST",
                    body: formData,
                });

                fetch(request)
                    .then(res => {
                        filepond.removeFiles();
                        if (res.status === 200) {
                            return res.blob();
                        } else {
                            throw new Error("Error");
                        }
                    })
                    .then(blob => {
                        saveBlobAsFile(blob, file);
                    });

                return {
                    abort: () => {
                        request.abort();
                        abort();
                    },
                };
            },


        },
    }
);

async function sweetChooseSize() {
    return await Swal.fire({
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
}

function saveBlobAsFile(blob, file) {
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = file.name;
    document.body.appendChild(a);
    a.click();
    a.remove();
}


document.querySelector(".button-process").addEventListener("click", () => {
    filepond.processFiles();
});
