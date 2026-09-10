import 'filepond/dist/filepond.min.css';
import "../app.js";
import '../../styles/resize.css';
import { processFileAction, saveBlobAsFile } from '../utils.js';
import { SubAppWithFilePond, type ProcessArgs } from './subappwithfilepond.js';


class ConvertApp extends SubAppWithFilePond {
    private _extensionSelectElement: HTMLSelectElement;

    constructor() {
        super(document.querySelector('input.filepond') as HTMLInputElement);
        this._extensionSelectElement = document.querySelector("#dest-extension") as HTMLSelectElement;
    }

    async processFile(
        fieldName: ProcessArgs[0],
        file: ProcessArgs[1],
        _metadata: ProcessArgs[2],
        load: ProcessArgs[3],
        error: ProcessArgs[4],
        _progress: ProcessArgs[5],
        _abort: ProcessArgs[6],
    ): Promise<void> {
        const destExtension = this._extensionSelectElement.value;

        const blob = await processFileAction(`/file/convert/${destExtension}`, fieldName, file, load, error);
        this._filePond.removeFiles();
        if (blob) {
            saveBlobAsFile(blob, file);
        }
    }

}

new ConvertApp();
