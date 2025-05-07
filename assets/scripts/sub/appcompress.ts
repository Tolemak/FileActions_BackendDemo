import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import Swal from 'sweetalert2';
import { saveBlobAsFile, sweetAlertErrorRequest } from '../utils.js';
import { SubAppWithFilePond } from './subappwithfilepond.js';


async function sweetChooseCompress() {
    return await Swal.fire({
        title: "Choose compress?",
        icon: "question",
        input: "range",
        inputAttributes: {
            min: "1",
            max: "100",
            step: "1"
        },
        inputValue: 50,
        showCancelButton: true,
        cancelButtonText: "Cancel",
    });
}

class CompressApp extends SubAppWithFilePond {
    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
    }

    async processFile(fieldName: string, file: File, _metadata, _load, _error, _progress, abort, _transfer, _options): Promise<void> {
        const ret = await sweetChooseCompress();
        if (ret.isDismissed) {
            abort();
            Swal.fire("Cancelled", "Cancelled", "error");
            return;
        }

        const formData = new FormData();
        formData.append(fieldName, file, file.name);

        const request = new Request(`/file/compress/${ret.value}`, {
            method: "POST",
            body: formData,
        });

        fetch(request)
            .then(res => {
                this._filePond.removeFiles();
                if (res.status === 200) {
                    return res.blob();
                } else {
                    sweetAlertErrorRequest(res);
                    throw new Error("Error");
                }
            })
            .then(blob => {
                saveBlobAsFile(blob, file);
            });
    }

}

new CompressApp();
