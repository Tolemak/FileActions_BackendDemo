import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import { saveBlobAsFile, sweetAlertErrorRequest } from '../utils.js';
import { SubAppWithFilePond } from './subappwithfilepond.js';


class ConvertApp extends SubAppWithFilePond {
    private _extensionSelectElement: HTMLSelectElement;

    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
        this._extensionSelectElement = document.querySelector("#dest-extension") as HTMLSelectElement;
    }

    async processFile(fieldName: string, file: File, _metadata, _load, _error, _progress, abort, _transfer, _options): Promise<void> {
        const destExtension = this._extensionSelectElement.value
        const formData = new FormData();
        formData.append(fieldName, file, file.name);

        const request = new Request(`/file/convert/${destExtension}`, {
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

new ConvertApp();
