import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import { processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond } from './subappwithfilepond.js';


class ConvertApp extends SubAppWithFilePond {
    private _extensionSelectElement: HTMLSelectElement;

    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
        this._extensionSelectElement = document.querySelector("#dest-extension") as HTMLSelectElement;
    }

    async processFile(fieldName: string, file: File, _metadata, load, error, _progress, abort, _transfer, _options): Promise<void> {
        const destExtension = this._extensionSelectElement.value;

        const blob = await processFileAction(`/file/convert/${destExtension}`, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (blob) {
            saveBlobAsFile(blob, file);
        }
    }

}

new ConvertApp();
